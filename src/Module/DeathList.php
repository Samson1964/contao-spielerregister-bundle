<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Module;

use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\PageOutOfRangeException;
use Contao\CoreBundle\Pagination\LegacyTemplatePaginationProxy;
use Contao\CoreBundle\Pagination\PaginationConfig;
use Contao\Database;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Pagination;
use Contao\System;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Datum;
use Schachbulle\ContaoSpielerregisterBundle\Classes\SpielerregisterModul;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Frontend-Modul "Sterbeliste" (auf schachbund.de die Gedenktafel).
 *
 * Listet die verstorbenen Personen des Registers auf, die jüngsten zuerst.
 * Die Moduleinstellung "Anzahl Monate" begrenzt den Zeitraum; 0 zeigt alle
 * Verstorbenen. Mit "Elemente pro Seite" wird die Liste auf mehrere Seiten
 * verteilt.
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
	 * Angezeigt werden nur aktive Personen, die als verstorben gekennzeichnet
	 * sind. Ist ein Zeitraum in Monaten eingestellt, fallen alle heraus, deren
	 * Sterbedatum davor liegt oder unbekannt ist. Ohne Zeitraum erscheinen auch
	 * Personen ohne Sterbedatum — wegen der absteigenden Sortierung am Ende der
	 * Liste. Fehlt das Geburtsdatum, steht in der Spalte "Alter" ein
	 * Fragezeichen.
	 *
	 * Gezählt und geblättert wird in der Datenbank (COUNT + LIMIT), damit bei
	 * mehreren tausend Verstorbenen nicht jede Zeile geladen wird.
	 *
	 * @throws PageNotFoundException Wenn eine Seitenzahl außerhalb der Liste
	 *                               aufgerufen wird; Contao zeigt dann die
	 *                               404-Seite, wie bei den Nachrichtenlisten
	 *
	 * @return void
	 */
	protected function compile()
	{
		$intMonate = (int) $this->spielerregister_deathlist_months;

		$strWhere = 'active = ? AND death = ?';
		$arrWerte = array(1, 1);

		if ($intMonate > 0)
		{
			$strWhere .= ' AND deathday >= ?';
			$arrWerte[] = date('Ymd', strtotime('-'.$intMonate.' month'));
		}

		$db = Database::getInstance();

		$intGesamt = (int) $db
			->prepare('SELECT COUNT(*) AS anzahl FROM tl_spielerregister WHERE '.$strWhere)
			->execute(...$arrWerte)
			->anzahl;

		// Template-Objekt anlegen
		$this->Template = new FrontendTemplate($this->strTemplate);
		$this->Template->pagination = '';

		$intLimit = 0;
		$intOffset = 0;
		$intProSeite = (int) $this->perPage;

		if ($intProSeite > 0 && $intGesamt > $intProSeite)
		{
			list($intLimit, $intOffset, $this->Template->pagination) = $this->blaettern($intGesamt, $intProSeite);
		}

		$objStatement = $db->prepare('SELECT * FROM tl_spielerregister WHERE '.$strWhere.' ORDER BY deathday DESC, surname1, firstname1');

		if ($intLimit > 0)
		{
			$objStatement->limit($intLimit, $intOffset);
		}

		$objRegister = $objStatement->execute(...$arrWerte);

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
		$this->Template->gesamt = $intGesamt;
	}

	/**
	 * Berechnet den Ausschnitt der aktuellen Seite und baut die Seitennavigation.
	 *
	 * Contao 5.7 hat die Klasse Contao\Pagination für veraltet erklärt (in
	 * Contao 6 entfällt sie) und bietet stattdessen den Dienst
	 * contao.pagination.factory. Contao 4.13 kennt den Dienst noch nicht. Darum
	 * wird der Dienst benutzt, wenn es ihn gibt, und sonst die alte Klasse —
	 * dasselbe Vorgehen wie im Nachrichtenmodul des jeweiligen Kerns. Der
	 * Seitenparameter heißt in beiden Fällen "page_d<Modul-ID>", mehrere
	 * Sterbelisten auf einer Seite blättern also unabhängig voneinander.
	 *
	 * @param int $intGesamt   Anzahl aller passenden Personen, größer als $intProSeite
	 * @param int $intProSeite Personen je Seite, größer als 0
	 *
	 * @throws PageNotFoundException Bei einer Seitenzahl außerhalb der Liste
	 *
	 * @return array{0: int, 1: int, 2: mixed} Anzahl der Zeilen dieser Seite,
	 *                                         Versatz ab der ersten Zeile und
	 *                                         die Seitennavigation (im Template
	 *                                         als Zeichenkette ausgebbar)
	 */
	protected function blaettern(int $intGesamt, int $intProSeite): array
	{
		$strParameter = 'page_d'.$this->id;
		$container = System::getContainer();

		if ($container->has('contao.pagination.factory'))
		{
			try
			{
				$objPagination = $container->get('contao.pagination.factory')->create(new PaginationConfig($strParameter, $intGesamt, $intProSeite));
			}
			catch (PageOutOfRangeException $e)
			{
				throw new PageNotFoundException('Page not found: '.Environment::get('uri'), 0, $e);
			}

			return array($objPagination->getPerPage(), $objPagination->getOffset(), new LegacyTemplatePaginationProxy($container->get('twig'), $objPagination));
		}

		$intSeite = (int) (Input::get($strParameter) ?? 1);

		if ($intSeite < 1 || $intSeite > max((int) ceil($intGesamt / $intProSeite), 1))
		{
			throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
		}

		$objPagination = new Pagination($intGesamt, $intProSeite, (int) Config::get('maxPaginationLinks'), $strParameter);

		return array($intProSeite, ($intSeite - 1) * $intProSeite, $objPagination->generate("\n  "));
	}
}
