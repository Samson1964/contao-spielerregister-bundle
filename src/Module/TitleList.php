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
use Contao\FrontendTemplate;
use Schachbulle\ContaoHelperBundle\Classes\Helper as HelperBundle;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Datum;
use Schachbulle\ContaoSpielerregisterBundle\Classes\SpielerregisterModul;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Frontend-Modul "FIDE-Titelliste".
 *
 * Listet alle Personen mit einem der vier FIDE-Titel (GM, IM, WGM, WIM) auf,
 * sortiert nach dem Alter bei der Verleihung – die jüngsten Titelträger stehen
 * also oben.
 */
class TitleList extends SpielerregisterModul
{
	/**
	 * @var string Template des Moduls
	 */
	protected $strTemplate = 'spielerregister_titlelist';

	/**
	 * @var string Text des Platzhalters in der Backend-Seitenstruktur
	 */
	protected $strPlatzhalter = '### SPIELERREGISTER TITELLISTE ###';

	/**
	 * Zuordnung der Listenart zu den Spalten in tl_spielerregister.
	 *
	 * Die Schlüssel stehen so auch im Auswahlfeld der Moduleinstellungen; je
	 * Titel gibt es ein Kennzeichen (…_title) und ein Datum (…_date).
	 *
	 * @var array<string, string>
	 */
	protected static $arrSpalten = array
	(
		'gm' => 'gm',   // Großmeister
		'im' => 'im',   // Internationale Meister
		'wg' => 'wgm',  // Großmeisterinnen
		'wi' => 'wim'   // Internationale Meisterinnen
	);

	/**
	 * Stellt die Daten für das Template zusammen.
	 *
	 * Für jede Person wird die Zeitspanne zwischen Geburtstag und Verleihung
	 * berechnet. Unvollständige Datumsangaben – im Register durchaus üblich –
	 * werden dabei auf den 1. Juni bzw. den Monatsersten gesetzt, damit
	 * überhaupt ein Wert entsteht; die Angabe ist dann näherungsweise.
	 *
	 * @return void
	 */
	protected function compile()
	{
		// Template-Objekt anlegen
		$this->Template = new FrontendTemplate($this->strTemplate);
		$this->Template->data = array();

		$strTitel = self::$arrSpalten[$this->spielerregister_titlelist] ?? '';

		if ('' === $strTitel)
		{
			return; // Keine Listenart ausgewählt
		}

		// Der Spaltenname stammt aus der obigen Zuordnung und nicht aus der
		// Eingabe, deshalb ist das Einsetzen in die Abfrage unbedenklich
		$objRegister = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister WHERE active = ? AND '.$strTitel.'_title = 1')
			->execute(1);

		$strDatumsfeld = $strTitel.'_date';
		$output = array();

		while ($objRegister->next())
		{
			$titeldatum = $objRegister->$strDatumsfeld;
			$objDatum = Datum::zeitspanne($objRegister->birthday, $titeldatum);

			$output[] = array
			(
				'id'               => $objRegister->id,
				'vorname'          => $objRegister->firstname1,
				'nachname'         => $objRegister->surname1,
				'tot'              => $objRegister->death,
				'link'             => Helper::getPlayerlink($objRegister->id),
				'geburtstag'       => Helper::getDate($objRegister->birthday),
				'sterbetag'        => Helper::getDate($objRegister->deathday),
				'titeldatum'       => Helper::getDate($titeldatum),
				'verleihungsalter' => null !== $objDatum ? $objDatum->y.' Jahr(e), '.$objDatum->m.' Monat(e), '.$objDatum->d.' Tag(e)' : '',
				'anzahlTage'       => null !== $objDatum ? $objDatum->days : 0
			);
		}

		$this->Template->data = HelperBundle::sortArrayByFields
		(
			$output,
			array
			(
				'anzahlTage' => array(SORT_ASC, SORT_NUMERIC)
			)
		);
	}
}
