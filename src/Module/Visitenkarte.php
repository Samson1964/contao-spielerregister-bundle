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
use Contao\StringUtil;
use Schachbulle\ContaoSpielerregisterBundle\Classes\SpielerregisterModul;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Visitenkarte as VisitenkartenGenerator;

/**
 * Frontend-Modul "Visitenkarte".
 *
 * Gibt eine Person aus dem Register in einer frei gestalteten Vorlage aus.
 * Person und Vorlage werden in den Moduleinstellungen gewählt; für eine
 * Kaderseite mit mehreren Spielern wird je Spieler ein Modul angelegt.
 */
class Visitenkarte extends SpielerregisterModul
{
	/**
	 * @var string Template des Moduls
	 */
	protected $strTemplate = 'spielerregister_visitenkarte';

	/**
	 * @var string Text des Platzhalters in der Backend-Seitenstruktur
	 */
	protected $strPlatzhalter = '### SPIELERREGISTER VISITENKARTE ###';

	/**
	 * @var array<string, mixed> Datensatz der Person, von generate() geladen
	 */
	protected $arrPerson = array();

	/**
	 * @var array<string, mixed> Datensatz der Vorlage, von generate() geladen
	 */
	protected $arrKarte = array();

	/**
	 * Lädt Person und Vorlage und erzeugt das Modul.
	 *
	 * Fehlt eines von beiden — nicht gewählt, gelöscht oder die Person
	 * deaktiviert —, gibt das Modul nichts aus, auch keinen leeren Rahmen.
	 *
	 * @return string Der Platzhalter im Backend, sonst die Ausgabe des Moduls
	 *                oder ein leerer String
	 */
	public function generate()
	{
		if ($this->isBackendRequest())
		{
			return parent::generate();
		}

		$objPerson = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister WHERE id = ? AND active = ?')
			->limit(1)
			->execute((int) $this->spielerregister_person, '1');

		$objKarte = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister_cards WHERE id = ?')
			->limit(1)
			->execute((int) $this->spielerregister_karte);

		if (!$objPerson->numRows || !$objKarte->numRows)
		{
			return '';
		}

		$this->arrPerson = $objPerson->row();
		$this->arrKarte = $objKarte->row();

		return parent::generate();
	}

	/**
	 * Übergibt die fertige Visitenkarte an das Template.
	 *
	 * @return void
	 */
	protected function compile()
	{
		$arrKarte = VisitenkartenGenerator::erzeuge($this->arrKarte, $this->arrPerson);

		$this->Template->inhalt = $arrKarte['inhalt'];
		$this->Template->hintergrund = '' !== $arrKarte['hintergrund'] ? StringUtil::specialchars($arrKarte['hintergrund']) : '';
		$this->Template->kartenklasse = '' !== $arrKarte['klasse'] ? ' '.StringUtil::specialchars($arrKarte['klasse']) : '';
	}
}
