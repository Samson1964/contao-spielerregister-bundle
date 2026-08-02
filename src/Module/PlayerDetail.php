<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Module;

use Contao\Database;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoHelperBundle\Classes\Helper as HelperBundle;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Bilder;
use Schachbulle\ContaoSpielerregisterBundle\Classes\SpielerregisterModul;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Spielerregister;

/**
 * Frontend-Modul "Spielerdetails".
 *
 * Zeigt eine einzelne Person mit Lebensdaten, Fotos und Weblinks an. Die
 * Person wird über das Auto-Item /player/<ID> ausgewählt; zusätzlich enthält
 * das Modul ein Suchformular, das über den Parameter psearch ausgewertet wird.
 */
class PlayerDetail extends SpielerregisterModul
{
	/**
	 * @var string Text des Platzhalters in der Backend-Seitenstruktur
	 */
	protected $strPlatzhalter = '### SPIELERREGISTER DETAILS ###';

	/**
	 * @var string|null ID des abgefragten Spielers aus der URL
	 */
	protected $playerid;

	/**
	 * @var string|null Suchbegriff aus dem Suchformular, klein geschrieben
	 */
	protected $playersearch;

	/**
	 * @var int Minimale Relevanz, ab der eine Person überhaupt angezeigt wird
	 */
	protected $minImportance = 1;

	/**
	 * Erzeugt das Modul und liest die Parameter aus der URL.
	 *
	 * Im Backend übernimmt die Basisklasse die Platzhalter-Darstellung. Im
	 * Frontend werden hier die Spieler-ID (Auto-Item "player") und der
	 * Suchbegriff (psearch) übernommen, weil compile() sie braucht.
	 *
	 * @return string Der Platzhalter im Backend, sonst die Ausgabe des Moduls
	 */
	public function generate()
	{
		if ($this->isBackendRequest())
		{
			return parent::generate();
		}

		// FE-Modus: URL mit allen möglichen Parametern auflösen
		$this->playerid = Input::get('player'); // Spieler-ID
		$this->playersearch = strtolower((string) Input::get('psearch'));

		return parent::generate(); // Weitermachen mit dem Modul
	}

	/**
	 * Stellt die Daten für das Template zusammen.
	 *
	 * Liest die Person zur angeforderten ID, wertet eine eventuelle Suche aus
	 * und bereitet die Fotos auf. Fotos stehen an zwei Stellen: als
	 * Dateiauswahl am Datensatz (multiSRC) und als eigene Datensätze in
	 * tl_spielerregister_images. Die Dateiauswahl hat Vorrang; nur wenn dort
	 * nichts hinterlegt ist, wird die Bildtabelle gelesen.
	 *
	 * @return void
	 */
	protected function compile()
	{
		global $objPage;

		// Weiterleitungsseite ermitteln
		$jumpTo = PageModel::findByPk($this->spielerregister_jumpTo);

		$objRegister = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister WHERE active = 1 AND importance >= ? AND id = ?')
			->limit(1)
			->execute($this->minImportance, $this->playerid);

		// Template-Objekt anlegen
		$this->Template = new FrontendTemplate('spielerregister_playerdetail');

		// Vom Template gelesene Variablen vorbelegen
		$this->Template->sucheaktiv = false;
		$this->Template->suchfehler = '';
		$this->Template->suchergebnis = array();
		$this->Template->slider = false;
		$this->Template->images = array();
		$this->Template->imageWidth = 0;
		$this->Template->imageHeight = 0;
		$this->Template->debug = '';

		// Link zur aktuellen Seite (inkl. Parameter) für das Suchformular. Das
		// mitgelieferte Template schickt das Formular mit action="" an die
		// aktuelle Adresse; die Variable ist deshalb nur für abgewandelte
		// Templates da und wird hier weiter gefüllt.
		//
		// Früher stand hier Environment::get('indexFreeRequest'); das greift
		// intern auf Environment::request() und ::script() zu, die Contao 5 für
		// veraltet erklärt hat. Der Symfony-Request liefert dieselbe Adresse und
		// funktioniert in Contao 4.13 genauso.
		$objRequest = System::getContainer()->get('request_stack')->getCurrentRequest();
		$this->Template->action = StringUtil::ampersand(null !== $objRequest ? $objRequest->getRequestUri() : '');

		// Suche auswerten
		if ($this->playersearch)
		{
			$this->Template->sucheaktiv = true; // Eine Suche wurde ausgelöst
			$suchlaenge = \strlen($this->playersearch); // Länge des Suchbegriffs

			if ($suchlaenge > 1)
			{
				// Sucht nach Suchbegriff über alle 4 Nachnamenfelder
				$objSuche = Database::getInstance()
					->prepare('SELECT * FROM tl_spielerregister WHERE active = 1 AND importance >= ? AND (surname1 LIKE ? OR surname2 LIKE ? OR surname3 LIKE ? OR surname4 LIKE ?) ORDER BY surname1 ASC')
					->execute($this->minImportance, '%'.$this->playersearch.'%', '%'.$this->playersearch.'%', '%'.$this->playersearch.'%', '%'.$this->playersearch.'%');

				$this->Template->suchtreffer = $objSuche->numRows;
				$this->Template->suchbegriff = $this->playersearch;

				$ergebnisliste = array();
				$zaehler = 0;

				while ($objSuche->next())
				{
					$ergebnisliste[$zaehler]['name'] = $objSuche->firstname1.' '.$objSuche->surname1;
					$ergebnisliste[$zaehler]['id'] = $objSuche->id;
					$ergebnisliste[$zaehler]['link'] = Helper::seitenUrl($jumpTo, '/player/'.$objSuche->id);

					// Lebensdaten für Ausgabe aufbereiten
					$birthday = $this->DatumToString($objSuche->birthday);
					$deathday = $this->DatumToString($objSuche->deathday);
					$temp = $birthday ? '* '.$birthday : '';

					if ($objSuche->death)
					{
						$temp = $temp ? $temp.'; &dagger;' : '&dagger;';
						$temp .= $deathday ? ' '.$deathday : ' unbekannt';
					}

					$ergebnisliste[$zaehler]['lebensdaten'] = $objSuche->hideLifedata ? '' : $temp;
					$zaehler++;
				}

				$this->Template->suchergebnis = $ergebnisliste;

				if ($objSuche->numRows == 0)
				{
					$this->Template->suchfehler = 'Keine Treffer für "<i>'.$this->playersearch.'</i>".';
				}
			}
			else
			{
				$this->Template->suchfehler = 'Ihr Suchbegriff "<i>'.$this->playersearch.'</i>" enthält zu wenig Zeichen.';
			}
		}

		if (!$objRegister->numRows)
		{
			return; // Keine Person gefunden, es bleibt beim Suchformular
		}

		$this->Template->id = $objRegister->id;
		$this->Template->nachname = $objRegister->surname1;
		$this->Template->vorname = $objRegister->firstname1;

		// Name für Ausgabe aufbereiten
		$this->Template->name = $objRegister->firstname1 ? $objRegister->firstname1.' '.$objRegister->surname1 : $objRegister->surname1;

		// Seitentitel modifizieren
		if (null !== $objPage)
		{
			$objPage->pageTitle = $this->Template->name.' | '.$objPage->pageTitle;
		}

		$this->Template->geburtstag = $objRegister->hideLifedata ? '' : $this->DatumToString($objRegister->birthday);
		$this->Template->verstorben = $objRegister->hideLifedata ? '' : $objRegister->death;
		$this->Template->todestag = $objRegister->hideLifedata ? '' : $this->DatumToString($objRegister->deathday);

		// Lebensdaten für Ausgabe aufbereiten
		$temp = ($this->Template->geburtstag && !$objRegister->hideLifedata) ? '* '.$this->Template->geburtstag : '';

		if ($objRegister->death && !$objRegister->hideLifedata)
		{
			$temp = $temp ? $temp.'; &dagger;' : '&dagger;';
			$temp .= $this->Template->todestag ? ' '.$this->Template->todestag : ' unbekannt';
		}

		$this->Template->lebensdaten = $objRegister->hideLifedata ? '' : $temp;
		$this->Template->bedeutung = $objRegister->importance;
		$this->Template->wikipedia = $objRegister->wikipedia;
		$this->Template->kurzinfo = $objRegister->shortinfo;
		// Die Spalte heißt longinfo; das Template erwartet die Variable langinfo.
		// Vorher stand hier $objRegister->langinfo – eine Spalte dieses Namens
		// gibt es nicht, deshalb blieb der ausführliche Text auf der Detailseite
		// immer leer.
		$this->Template->langinfo = $objRegister->longinfo;
		$this->Template->chess365_id = $objRegister->chess365_id;
		$this->Template->chess_id = $objRegister->chess_id;
		$this->Template->chessgames_id = $objRegister->chessgames_id;

		// Bilder aus multiSRC holen
		$images = Spielerregister::Bilder($objRegister->multiSRC);

		if ($images)
		{
			// Größte Breite und Höhe ermitteln, danach richtet sich der Slider
			$breite = 0;
			$hoehe = 0;

			foreach ($images as $bild)
			{
				if ($bild['imageWidth'] > $breite)
				{
					$breite = $bild['imageWidth'];
				}

				if ($bild['imageHeight'] > $hoehe)
				{
					$hoehe = $bild['imageHeight'];
				}
			}

			$this->Template->imageWidth = $breite;
			$this->Template->imageHeight = $hoehe;
			$this->Template->slider = true;
			$this->Template->images = $images;

			$this->ladeSliderAssets();

			return;
		}

		// Keine Dateiauswahl am Datensatz: Fotos aus tl_spielerregister_images lesen
		$this->ladeBildtabelle($objRegister->id);
	}

	/**
	 * Liest die Fotos aus tl_spielerregister_images und füllt das Template.
	 *
	 * Diese Tabelle ist der ältere Weg der Bildpflege: Jedes Foto ist ein
	 * eigener Datensatz mit Titel, Jahr und Bildrechten. Angezeigt werden alle
	 * aktiven Fotos, das jüngste zuerst.
	 *
	 * @param int|string $intPid ID der Person in tl_spielerregister
	 *
	 * @return void
	 */
	protected function ladeBildtabelle($intPid): void
	{
		$images = array();

		// Fotos laden, das jüngste zuerst
		$objImage = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister_images WHERE pid = ? AND active = 1 ORDER BY imagedate DESC')
			->execute($intPid);

		if (!$objImage->numRows)
		{
			$this->Template->bildoriginal = '';
			$this->Template->bildvorschau = '';
			$this->Template->bildjahr = '';
			$this->Template->bildtitel = '';
			$this->Template->bildquelle = '';
			$this->Template->caption = '';

			return;
		}

		$this->ladeSliderAssets();

		while ($objImage->next())
		{
			$objFile = FilesModel::findByPk($objImage->singleSRC);

			if (null === $objFile)
			{
				continue; // Datei wurde zwischenzeitlich gelöscht
			}

			$caption = HelperBundle::replaceCopyright(self::erzeugeBildunterschrift($objImage));

			$images[$objFile->path] = array
			(
				'id'        => $objFile->id,
				'uuid'      => $objFile->uuid,
				'name'      => $objFile->basename,
				'singleSRC' => $objFile->path,
				'image'     => $objFile->path,
				'alt'       => '',
				'imageUrl'  => '',
				'imageID'   => $objFile->id,
				'caption'   => $caption,
				'imageCaption' => $caption,
				'imageSize' => '',
				'thumb'     => Bilder::vorschaubild((string) $objFile->path, 180, 180),
				'thumbnail' => Bilder::vorschaubild((string) $objFile->path, 180, 180)
			);
		}

		if (!$images)
		{
			return;
		}

		// Das zuletzt gelesene, also älteste Foto steht am Ende; für die
		// Einzelausgabe wird das jüngste gebraucht
		$erstes = reset($images);

		$this->Template->bildoriginal = $erstes['singleSRC'];
		$this->Template->bildvorschau = $erstes['thumbnail'];
		$this->Template->bildbreite = 0;
		$this->Template->bildjahr = '';
		$this->Template->bildtitel = '';
		$this->Template->bildquelle = '';
		$this->Template->caption = $erstes['caption'];
		$this->Template->slider = true;
		$this->Template->images = $images;
	}

	/**
	 * Setzt CSS und JavaScript des Bilderkarussells auf die Seite.
	 *
	 * @return void
	 */
	protected function ladeSliderAssets(): void
	{
		$GLOBALS['TL_CSS'][] = 'bundles/contaospielerregister/css/js-image-slider.css';
		$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaospielerregister/js/js-image-slider.js';
	}

	/**
	 * Baut die Bildunterschrift aus Titel, Jahr und Bildrechten zusammen.
	 *
	 * Die Bildrechte kommen in eckige Klammern, weil der Helper des
	 * Helper-Bundles sie daran erkennt und als eigenen Block voranstellt.
	 *
	 * @param object $objImage Datensatz aus tl_spielerregister_images
	 *
	 * @return string Die Bildunterschrift, bei fehlenden Angaben ein leerer String
	 */
	protected static function erzeugeBildunterschrift($objImage): string
	{
		$caption = (string) $objImage->title;

		if ($objImage->year)
		{
			$caption = $caption ? $caption.' ('.$objImage->year.')' : (string) $objImage->year;
		}

		if ($objImage->copyright)
		{
			$caption .= '['.$objImage->copyright.']';
		}

		return $caption;
	}
}
