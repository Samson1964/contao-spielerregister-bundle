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
use Schachbulle\ContaoSpielerregisterBundle\Classes\SpielerregisterModul;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Frontend-Modul "Ehrungslisten".
 *
 * Gibt je nach Moduleinstellung die Liste der Ehrenpräsidenten, Ehrenmitglieder
 * oder der Träger einer der Ehrennadeln, -plaketten und -urkunden des Deutschen
 * Schachbundes aus, jeweils absteigend nach dem Jahr der Verleihung.
 */
class HonorList extends SpielerregisterModul
{
	/**
	 * @var string Text des Platzhalters in der Backend-Seitenstruktur
	 */
	protected $strPlatzhalter = '### SPIELERREGISTER EHRUNGSLISTE ###';

	/**
	 * Zuordnung der Listenart zur Spalte in tl_spielerregister.
	 *
	 * Die Schlüssel stehen so auch im Auswahlfeld der Moduleinstellungen.
	 *
	 * @var array<string, string>
	 */
	protected static $arrSpalten = array
	(
		'pr' => 'honorpresident',   // Ehrenpräsidenten
		'me' => 'honormember',      // Ehrenmitglieder
		'go' => 'honorgoldpin',     // Goldene Ehrennadel
		'si' => 'honorsilverpin',   // Silberne Ehrennadel
		'gb' => 'honorgoldbadge',   // Goldene Ehrenplakette
		'sb' => 'honorsilverbadge', // Silberne Ehrenplakette
		'le' => 'honorletter',      // Ehrenbrief
		'pl' => 'honorplate',       // Ehrenteller
		'bm' => 'honormedal'        // Bundesmedaille
	);

	/**
	 * Stellt die Daten für das Template zusammen.
	 *
	 * Ist in den Moduleinstellungen keine (gültige) Listenart hinterlegt,
	 * bleibt die Ausgabe leer – eine Abfrage ohne Spaltennamen würde sonst zu
	 * einem SQL-Fehler führen.
	 *
	 * @return void
	 */
	protected function compile()
	{
		// Template-Objekt anlegen
		$this->Template = new FrontendTemplate('spielerregister_honorlist');
		$this->Template->data = array();

		$strSpalte = self::$arrSpalten[$this->spielerregister_honorListtype] ?? '';

		if ('' === $strSpalte)
		{
			return; // Keine Listenart ausgewählt
		}

		// Der Spaltenname stammt aus der obigen Zuordnung und nicht aus der
		// Eingabe, deshalb ist das Einsetzen in die Abfrage unbedenklich
		$objRegister = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister WHERE active = 1 AND '.$strSpalte.' != \'\' ORDER BY '.$strSpalte.' DESC')
			->execute();

		$output = array();

		while ($objRegister->next())
		{
			$output[] = array
			(
				'id'       => $objRegister->id,
				'vorname'  => $objRegister->firstname1,
				'nachname' => $objRegister->surname1,
				'tot'      => $objRegister->death,
				'jahr'     => $objRegister->$strSpalte,
				'link'     => Helper::getPlayerlink($objRegister->id),
				'bild'     => '' // Vorschaubilder sind in dieser Liste abgeschaltet
			);
		}

		$this->Template->data = $output;
	}
}
