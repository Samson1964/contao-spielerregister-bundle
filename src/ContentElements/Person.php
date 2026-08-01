<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\ContentElements;

use Contao\ContentElement;
use Contao\Database;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Inhaltselement "Person aus dem Spielerregister".
 *
 * Gibt eine im Register gepflegte Person mit Lebensdaten, Kurzinfo und
 * Weblinks aus, ohne dass die Daten im Artikel doppelt gepflegt werden müssen.
 */
class Person extends ContentElement
{
	/**
	 * @var string Template des Inhaltselements
	 */
	protected $strTemplate = 'ce_spielerregister_person';

	/**
	 * Stellt die Daten für das Template zusammen.
	 *
	 * Enthält die Überschrift den Platzhalter %s, wird dort der Name der Person
	 * eingesetzt. Ist keine Person ausgewählt oder wurde der Datensatz
	 * gelöscht, bleibt die Ausgabe bis auf die Überschrift leer.
	 *
	 * @return void
	 */
	protected function compile()
	{
		// Vom Template gelesene Variablen vorbelegen
		$this->Template->addImage = false;
		$this->Template->addBefore = false;
		$this->Template->name = '';
		$this->Template->lebensdaten = '';
		$this->Template->kurzinfo = '';
		$this->Template->linkdaten = '';
		$this->Template->langinfo = '';

		if (!$this->spielerregister_id)
		{
			return; // Keine Person ausgewählt
		}

		$objPerson = Database::getInstance()
			->prepare("SELECT * FROM tl_spielerregister WHERE id=?")
			->execute($this->spielerregister_id);

		if (!$objPerson->numRows)
		{
			return; // Datensatz existiert nicht mehr
		}

		$strName = trim($objPerson->firstname1.' '.$objPerson->surname1);

		// Überschrift auf Platzhalter %s prüfen und Namen einsetzen
		if (false !== strpos((string) $this->headline, '%s'))
		{
			$this->headline = sprintf($this->headline, $strName);
		}

		// Daten aus tl_spielerregister in das Template schreiben
		$this->Template->headline = $this->headline;
		$this->Template->name = $strName;
		$this->Template->lebensdaten = $objPerson->hideLifedata ? '' : Helper::getLebensdaten($objPerson->birthday, $objPerson->birthplace, $objPerson->death, $objPerson->deathday, $objPerson->deathplace);
		$this->Template->kurzinfo = $objPerson->shortinfo;
		$this->Template->linkdaten = Helper::getLinks($objPerson->links);
		$this->Template->langinfo = $objPerson->longinfo;
	}
}
