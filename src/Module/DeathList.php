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
use Schachbulle\ContaoSpielerregisterBundle\Classes\Datum;
use Schachbulle\ContaoSpielerregisterBundle\Classes\SpielerregisterModul;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Frontend-Modul "Sterbeliste".
 *
 * Listet die zuletzt verstorbenen Personen des Registers auf, die jüngsten
 * zuerst. Wie weit zurückgeschaut wird, legt die Moduleinstellung "Anzahl
 * Monate" fest.
 */
class DeathList extends SpielerregisterModul
{
	/**
	 * @var string Template des Moduls
	 */
	protected $strTemplate = 'spielerregister_deathlist';

	/**
	 * @var string Text des Platzhalters in der Backend-Seitenstruktur
	 */
	protected $strPlatzhalter = '### SPIELERREGISTER STERBELISTE ###';

	/**
	 * Stellt die Daten für das Template zusammen.
	 *
	 * Angezeigt werden nur Personen, die als verstorben gekennzeichnet sind und
	 * deren Sterbedatum im gewählten Zeitraum liegt. Fehlt bei einer Person das
	 * Geburtsdatum, steht in der Spalte "Alter" ein Fragezeichen.
	 *
	 * @return void
	 */
	protected function compile()
	{
		$startdatum = date('Ymd', strtotime('-'.(int) $this->spielerregister_deathlist_months.' month'));

		$objRegister = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister WHERE active = ? AND death = ? AND deathday >= ? ORDER BY deathday DESC')
			->execute(1, 1, $startdatum);

		// Template-Objekt anlegen
		$this->Template = new FrontendTemplate($this->strTemplate);

		$output = array();

		while ($objRegister->next())
		{
			$alter = Datum::alterZwischen($objRegister->birthday, $objRegister->deathday);

			$output[] = array
			(
				'id'        => $objRegister->id,
				'vorname'   => $objRegister->firstname1,
				'nachname'  => $objRegister->surname1,
				'sterbetag' => Helper::getDate($objRegister->deathday),
				'link'      => Helper::getPlayerlink($objRegister->id),
				'kurzinfo'  => $objRegister->shortinfo,
				'alter'     => (false !== $alter) ? $alter : '?'
			);
		}

		$this->Template->data = $output;
	}
}
