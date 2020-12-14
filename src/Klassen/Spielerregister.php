<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Benutzerdefinierten Namespace festlegen, damit die Klasse ersetzt werden kann
 */
namespace Schachbulle\ContaoSpielerregisterBundle\Klassen;

class Spielerregister
{

	/**
	 * Gibt ein Array mit den Spielerbildern zurück
	 * @param string    string      Serialisiertes Array mit den Daten
	 * @param boolean   newestImage false = alle Bilder zurückgeben, true = nur 1 Bild zurückgeben (das jüngste)
	 * @return array
	 */
	public static function Bilder($string, $newestImage = false)
	{
		global $objPage;

		// Serialisierten Bilderstring in Array umwandeln und Bilder suchen
		$multiSRC = unserialize($string);
		if(is_array($multiSRC))
		{
			$objFiles = \FilesModel::findMultipleByUuids($multiSRC); // Bilder aus Datenbank laden
		}
		else
		{
			return false; // Keine Bilder übergeben
		}

		$images = array();
		$imageSize = unserialize($GLOBALS['TL_CONFIG']['spielerregister_imageSize']);

		if($objFiles)
		{
			// Alle Datensätze iterieren
			while($objFiles->next())
			{
				
				$objItem = \FilesModel::findByUuid($objFiles->uuid); // Objekt vom aktuellen Datensatz erstellen

				if($objFiles->type == 'file')
				{
					// Datei

					$objBild = new \stdClass();
					\Controller::addImageToTemplate($objBild, array('singleSRC' => $objFiles->path, 'size' => $imageSize), \Config::get('maxImageWidth'), null, $objItem); // aktuelles $objekt übergeben!
					//\Controller::addImageToTemplate($objBild, array('singleSRC' => $objFiles->path, 'size' => $imageSize), \Config::get('maxImageWidth'));

					// Bild hinzufügen
					$images[] = array
					(
						'imageID'      => mt_rand(),
						'imageWidth'   => $objBild->arrSize[0],
						'imageHeight'  => $objBild->arrSize[1],
						'tstamp'       => $objFiles->tstamp,
						'image'        => $objBild->singleSRC,
						'imageSize'    => $objBild->imgSize,
						'imageTitle'   => $objBild->imageTitle,
						'imageAlt'     => $objBild->alt,
						'imageCaption' => \Schachbulle\ContaoHelperBundle\Classes\Helper::replaceCopyright($objBild->caption),
						'thumbnail'    => $objBild->src
					);
				}
				else
				{
					// Ordner
					$objSubfiles = \FilesModel::findByPid($objFiles->uuid); // Dateien des Ordners finden

					if($objSubfiles->type == 'file')
					{
						// Alle Datensätze iterieren
						while($objSubfiles->next())
						{

							$objItem = \FilesModel::findByUuid($objSubfiles->uuid); // Objekt vom aktuellen Datensatz erstellen

							// Datei
							$objBild = new \stdClass();
							\Controller::addImageToTemplate($objBild, array('singleSRC' => $objSubfiles->path, 'size' => $imageSize), \Config::get('maxImageWidth'), null, $objItem); // aktuelles $objekt übergeben!
							//\Controller::addImageToTemplate($objBild, array('singleSRC' => $objSubfiles->path, 'size' => $imageSize), \Config::get('maxImageWidth'));

							// Bild hinzufügen
							$images[] = array
							(
								'imageID'      => mt_rand(),
								'imageWidth'   => $objBild->arrSize[0],
								'imageHeight'  => $objBild->arrSize[1],
								'tstamp'       => $objSubfiles->tstamp,
								'image'        => $objBild->singleSRC,
								'imageSize'    => $objBild->imgSize,
								'imageTitle'   => $objBild->imageTitle,
								'imageAlt'     => $objBild->alt,
								'imageCaption' => \Schachbulle\ContaoHelperBundle\Classes\Helper::replaceCopyright($objBild->caption),
								'thumbnail'    => $objBild->src
							);
						}
					}

				}

			}
		}

		if($newestImage)
		{
			// Nur das neueste Bild zurückgeben
			$zeitstempel = 0; // Zeitstempel initialisieren, um aktuellstes Bild zu speichern
			$markiert = -1; // Damit wird der Index gesichert
			for($x = 0; $x < count($images); $x++)
			{
				if($images[$x]['tstamp'] > $zeitstempel)
				{
					// Aktuelleres Bild gefunden und merken
					$markiert = $x;
					$zeitstempel = $images[$x]['tstamp'];
				}
			}
			// Ausgabe
			if($markiert == -1) return false;
			else
			{
				return $images[$markiert];
			}
		}

		return $images; // Bilder-Array zurückgeben

	}

	public static function ExtractCaption($string)
	{
		$parts = explode('_', $string); // Dateiname trennen

		foreach($parts as $part)
		{
			if(is_numeric($part)) return $part;
		}

		return '';

	}

	public function ReplacePlayer($strTag)
	{

		$arrSplit = explode('::', $strTag);

		if($arrSplit[0] == 'register' || $arrSplit[0] == 'cache_register')
		{
			// Inhalt des Inserttags trennen
			$data = explode('|', $arrSplit[1]);

			// Template-Objekt erzeugen
			$this->Template = new \FrontendTemplate('spielerregister_infobox');

			$found = false; // Spieler erstmal auf nicht gefunden setzen
			// Parameter prüfen
			if(is_int($data[0]))
			{
				// Übergeben wurde die Spielerregister-ID, SQL-Abfrage zusammenbauen
				$objPlayer = \Database::getInstance()->prepare("SELECT * FROM tl_spielerregister WHERE id=?")
				                                     ->execute($data[0]);
			}
			elseif($data[0])
			{
				// Übergeben wurde ein String, Trennung nach Komma versuchen
				$teile = explode(',', $data[0]);
				if(count($teile) == 1)
				{
					// Trennung nach Leerzeichen versuchen
					$teile = explode(' ', $data[0]);
					if(count($teile) == 1)
					{
						// Nur 1 Wort vorhanden
					}
					elseif(count($teile) > 1)
					{
						$objPlayer = \Database::getInstance()->prepare("SELECT * FROM tl_spielerregister WHERE surname1 = ? AND firstname1 = ?")
						                                     ->execute($teile[1], $teile[0]);
					}
				}
			}

			// Suche auswerten
			if($objPlayer && $objPlayer->numRows == 1)
			{
				$this->Template->playername = $data[1] ? $data[1] : $data[0];
				$this->Template->playerid = $objPlayer->id;
			}
			else
			{
				// Template mit Vorgabewerten füllen
				$this->Template->playername = $data[1] ? $data[1] : $data[0];
				$this->Template->gefunden = false;
			}

			// Geparstes Template zurückgeben
			return $this->Template->parse();
		}

		// Nicht unser Insert-Tag
		return false;

	}

}
