<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Klassen;

use Contao\Config;
use Contao\Database;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoHelperBundle\Classes\Helper as HelperBundle;

/**
 * Bildaufbereitung und Insert-Tag {{register::…}} des Spielerregisters.
 */
class Spielerregister
{
	/**
	 * @var FrontendTemplate|null Template der Infobox, von ReplacePlayer() gefüllt
	 */
	protected $Template;

	/**
	 * Bereitet die im Register hinterlegten Fotos einer Person auf.
	 *
	 * Erwartet den Inhalt der Spalte multiSRC, also ein serialisiertes Array
	 * mit Datei- und Ordner-UUIDs. Ordner werden eine Ebene tief aufgelöst,
	 * verschachtelte Unterordner also nicht berücksichtigt.
	 *
	 * Die Bilder werden über das Contao-Bildstudio in der unter System ->
	 * Einstellungen gewählten Größe (spielerregister_imageSize) erzeugt. Der
	 * frühere Weg über Controller::addImageToTemplate() ist entfallen, weil es
	 * die Methode in Contao 5 nicht mehr gibt.
	 *
	 * @param mixed $string      Serialisiertes Array mit den UUIDs aus multiSRC
	 * @param bool  $newestImage False: alle Bilder zurückgeben; true: nur das
	 *                           jüngste Bild (nach Zeitstempel der Datei)
	 *
	 * @return array|false Liste der Bilddaten, bei $newestImage ein einzelner
	 *                     Bilddatensatz, oder false, wenn keine Bilder
	 *                     hinterlegt sind bzw. keines erzeugt werden konnte
	 */
	public static function Bilder($string, bool $newestImage = false)
	{
		// Serialisierten Bilderstring in Array umwandeln und Bilder suchen
		$multiSRC = StringUtil::deserialize($string);

		if (!\is_array($multiSRC) || !$multiSRC)
		{
			return false; // Keine Bilder übergeben
		}

		$objFiles = FilesModel::findMultipleByUuids($multiSRC);

		if (null === $objFiles)
		{
			return false; // Keine der UUIDs steckt (noch) in der Dateiverwaltung
		}

		$images = array();
		$imageSize = StringUtil::deserialize(Config::get('spielerregister_imageSize'));

		while ($objFiles->next())
		{
			if ($objFiles->type == 'file')
			{
				// Einzelne Datei
				$image = self::erzeugeBild((string) $objFiles->path, $imageSize, (int) $objFiles->tstamp);

				if ($image)
				{
					$images[] = $image;
				}

				continue;
			}

			// Ordner: alle darin liegenden Dateien einsammeln
			$objSubfiles = FilesModel::findByPid($objFiles->uuid);

			if (null === $objSubfiles)
			{
				continue;
			}

			while ($objSubfiles->next())
			{
				// Unterordner werden übersprungen, es wird nur eine Ebene gelesen
				if ($objSubfiles->type != 'file')
				{
					continue;
				}

				$image = self::erzeugeBild((string) $objSubfiles->path, $imageSize, (int) $objSubfiles->tstamp);

				if ($image)
				{
					$images[] = $image;
				}
			}
		}

		if ($newestImage)
		{
			// Nur das neueste Bild zurückgeben
			$zeitstempel = 0; // Zeitstempel initialisieren, um aktuellstes Bild zu speichern
			$markiert = -1; // Damit wird der Index gesichert

			for ($x = 0; $x < \count($images); $x++)
			{
				if ($images[$x]['tstamp'] > $zeitstempel)
				{
					// Aktuelleres Bild gefunden und merken
					$markiert = $x;
					$zeitstempel = $images[$x]['tstamp'];
				}
			}

			return ($markiert == -1) ? false : $images[$markiert];
		}

		return $images; // Bilder-Array zurückgeben
	}

	/**
	 * Erzeugt aus einer Bilddatei den Datensatz für die Templates.
	 *
	 * Die Schlüssel des Rückgabe-Arrays sind so benannt, wie die Templates des
	 * Bundles sie erwarten: "image" ist der Pfad zum Originalbild (Ziel der
	 * Lightbox), "thumbnail" das erzeugte Vorschaubild.
	 *
	 * @param string            $strPath   Pfad der Datei, relativ zum Projektverzeichnis
	 * @param mixed             $imageSize Bildgröße aus den Einstellungen (ID einer
	 *                                     Bildgröße oder Array aus Breite, Höhe und Modus)
	 * @param int               $intTstamp Zeitstempel der Datei, wird für die Suche
	 *                                     nach dem jüngsten Bild gebraucht
	 *
	 * @return array|null Die Bilddaten, oder null wenn die Datei fehlt oder sich
	 *                    nicht in ein Bild umwandeln lässt
	 */
	protected static function erzeugeBild(string $strPath, $imageSize, int $intTstamp): ?array
	{
		$figure = System::getContainer()
			->get('contao.image.studio')
			->createFigureBuilder()
			->fromPath($strPath)
			->setSize($imageSize)
			->buildIfResourceExists();

		if (null === $figure)
		{
			return null;
		}

		$data = $figure->getLegacyTemplateData();

		return array
		(
			'imageID'      => mt_rand(),
			'imageWidth'   => $data['arrSize'][0] ?? 0,
			'imageHeight'  => $data['arrSize'][1] ?? 0,
			'tstamp'       => $intTstamp,
			'image'        => $data['singleSRC'] ?? $strPath,
			'imageSize'    => $data['imgSize'] ?? '',
			'imageTitle'   => $data['imageTitle'] ?? '',
			'imageAlt'     => $data['alt'] ?? '',
			'imageCaption' => HelperBundle::replaceCopyright($data['caption'] ?? ''),
			'thumbnail'    => $data['src'] ?? ''
		);
	}

	/**
	 * Zieht die erste Zahl aus einem Dateinamen heraus.
	 *
	 * Gedacht für Dateinamen wie "lasker_1921_portrait.jpg", aus denen sich die
	 * Jahreszahl als Bildunterschrift gewinnen lässt.
	 *
	 * @param string $string Dateiname mit Unterstrichen als Trenner
	 *
	 * @return string Der erste rein numerische Bestandteil, sonst ein leerer String
	 */
	public static function ExtractCaption($string): string
	{
		$parts = explode('_', (string) $string); // Dateiname trennen

		foreach ($parts as $part)
		{
			if (is_numeric($part))
			{
				return $part;
			}
		}

		return '';
	}

	/**
	 * Ersetzt den Insert-Tag {{register::Spieler|Anzeigename}} durch die Infobox.
	 *
	 * Als Spieler wird entweder die Spielerregister-ID oder ein Name in der
	 * Form "Nachname, Vorname" bzw. "Vorname Nachname" erwartet. Der optionale
	 * zweite Teil hinter dem senkrechten Strich legt fest, welcher Text im
	 * Frontend verlinkt wird; ohne Angabe wird der Suchbegriff angezeigt.
	 *
	 * @param string $strTag Der komplette Insert-Tag ohne die geschweiften Klammern
	 *
	 * @return string|false Das geparste Template, oder false wenn es sich nicht
	 *                      um einen Insert-Tag dieser Erweiterung handelt
	 */
	public function ReplacePlayer($strTag)
	{
		$arrSplit = explode('::', (string) $strTag);

		if ($arrSplit[0] != 'register' && $arrSplit[0] != 'cache_register')
		{
			return false; // Nicht unser Insert-Tag
		}

		// Inhalt des Inserttags trennen
		$data = explode('|', $arrSplit[1] ?? '');

		// Template-Objekt erzeugen
		$this->Template = new FrontendTemplate('spielerregister_infobox');

		// Alle vom Template gelesenen Variablen vorbelegen, sonst greift es
		// auf undefinierte Werte zu
		$this->Template->playerid = 0;
		$this->Template->playername = '';
		$this->Template->lebensdaten = '';
		$this->Template->kurzinfo = '';
		$this->Template->wikipedia = '';
		$this->Template->slider = false;
		$this->Template->images = array();
		$this->Template->gefunden = false;

		$objPlayer = null; // Erstmal kein Spieler gefunden

		// Parameter prüfen
		// Hinweis: explode() liefert immer Strings, deshalb ctype_digit()
		// statt is_int() – sonst wird die ID-Suche nie erreicht
		if (ctype_digit((string) $data[0]))
		{
			// Übergeben wurde die Spielerregister-ID, SQL-Abfrage zusammenbauen
			$objPlayer = Database::getInstance()
				->prepare("SELECT * FROM tl_spielerregister WHERE id=?")
				->execute($data[0]);
		}
		elseif ($data[0])
		{
			// Übergeben wurde ein Name, Trennung nach Komma versuchen
			$teile = explode(',', $data[0]);

			if (\count($teile) > 1)
			{
				// Schreibweise "Nachname, Vorname"
				$objPlayer = Database::getInstance()
					->prepare("SELECT * FROM tl_spielerregister WHERE surname1 = ? AND firstname1 = ?")
					->execute(trim($teile[0]), trim($teile[1]));
			}
			else
			{
				// Trennung nach Leerzeichen versuchen
				$teile = explode(' ', $data[0]);

				if (\count($teile) > 1)
				{
					// Schreibweise "Vorname Nachname"
					$objPlayer = Database::getInstance()
						->prepare("SELECT * FROM tl_spielerregister WHERE surname1 = ? AND firstname1 = ?")
						->execute(trim($teile[1]), trim($teile[0]));
				}
			}
		}

		// Suche auswerten
		$strAnzeige = ($data[1] ?? '') !== '' ? $data[1] : $data[0];

		$this->Template->playername = $strAnzeige;

		if ($objPlayer && $objPlayer->numRows == 1)
		{
			$this->Template->playerid = $objPlayer->id;
			$this->Template->gefunden = true;

			// Inhalt der Lightbox füllen. Ohne diese Zuweisungen blieb die Box
			// bis auf den Namen leer, obwohl das Template die Daten ausgibt.
			$this->Template->lebensdaten = $objPlayer->hideLifedata ? '' : Helper::getLebensdaten($objPlayer->birthday, $objPlayer->birthplace, $objPlayer->death, $objPlayer->deathday, $objPlayer->deathplace);
			$this->Template->kurzinfo = $objPlayer->shortinfo;
			$this->Template->wikipedia = $objPlayer->wikipedia;

			$images = self::Bilder($objPlayer->multiSRC);

			if ($images)
			{
				$this->Template->slider = true;
				$this->Template->images = $images;
			}
		}

		// Geparstes Template zurückgeben
		return $this->Template->parse();
	}
}
