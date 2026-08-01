<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Classes;

use Contao\BackendTemplate;
use Contao\Module;
use Contao\System;

/**
 * Gemeinsame Basis der Frontend-Module des Spielerregisters.
 *
 * Übernimmt die Platzhalter-Darstellung im Backend sowie die Datums- und
 * Altersberechnungen, die vorher in jedem Modul einzeln standen.
 */
abstract class SpielerregisterModul extends Module
{
	/**
	 * @var string Text des Platzhalters in der Backend-Seitenstruktur
	 */
	protected $strPlatzhalter = '### SPIELERREGISTER ###';

	/**
	 * @var array<int, string> Monatsnamen, Index 1 = Januar
	 */
	protected $monatsname = Datum::MONATSNAMEN;

	/**
	 * Erzeugt das Modul.
	 *
	 * Im Backend wird statt der Ausgabe nur ein Platzhalter mit dem Modulnamen
	 * gezeigt – die Listen ließen sich dort ohnehin nicht sinnvoll darstellen
	 * und die Abfragen wären reine Last.
	 *
	 * @return string Der Platzhalter im Backend, sonst die Ausgabe des Moduls
	 */
	public function generate()
	{
		if ($this->isBackendRequest())
		{
			$objTemplate = new BackendTemplate('be_spielerregister');

			$objTemplate->wildcard = $this->strPlatzhalter;
			$objTemplate->title = $this->name;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}

		return parent::generate(); // Weitermachen mit dem Modul
	}

	/**
	 * Stellt fest, ob die Anfrage aus dem Backend kommt.
	 *
	 * Ersetzt die in Contao 5 entfallene Konstante TL_MODE. Ohne aktiven
	 * Request – etwa auf der Kommandozeile – gilt die Anfrage als Frontend,
	 * weil dort auch kein Backend-Platzhalter gebraucht wird.
	 *
	 * @return bool True, wenn die Anfrage im Backend-Bereich läuft
	 */
	protected function isBackendRequest(): bool
	{
		$container = System::getContainer();

		if (null === $container || !$container->has('request_stack'))
		{
			return false;
		}

		$request = $container->get('request_stack')->getCurrentRequest();

		if (null === $request)
		{
			return false;
		}

		return $container->get('contao.routing.scope_matcher')->isBackendRequest($request);
	}

	/**
	 * Formatiert ein Datum aus der Datenbank für die Ausgabe.
	 *
	 * Kurzschreibweise für Datum::formatiere(); gerechnet wird dort, damit sich
	 * die Datumslogik ohne Contao prüfen lässt.
	 *
	 * @param mixed  $datum   Datum als JJJJMMTT
	 * @param string $strLeer Rückgabewert, wenn im Datum nichts Verwertbares steht
	 *
	 * @return string Datum als TT.MM.JJJJ, MM.JJJJ oder JJJJ
	 */
	protected function DatumToString($datum, string $strLeer = ''): string
	{
		return Datum::formatiere($datum, $strLeer);
	}

	/**
	 * Liefert Tag und Monatsnamen eines Datums, also zum Beispiel "4. Juli".
	 *
	 * @param mixed $datum Datum als JJJJMMTT
	 *
	 * @return string Tag und ausgeschriebener Monat; bei fehlendem Monat bleibt
	 *                der Monatsname leer
	 */
	protected function LadeTag($datum): string
	{
		return Datum::monatstag($datum);
	}

	/**
	 * Liefert die Jahreszahl eines Datums.
	 *
	 * @param mixed $datum Datum als JJJJMMTT
	 *
	 * @return string Die vierstellige Jahreszahl
	 */
	protected function LadeJahr($datum): string
	{
		return Datum::jahr($datum);
	}

	/**
	 * Berechnet, der wievielte Jahrestag im betrachteten Zeitraum ansteht.
	 *
	 * Der Zeitraum kann über den Jahreswechsel hinausgehen. Deshalb wird
	 * anhand des Monats entschieden, ob der Jahrestag noch ins Anfangsjahr
	 * oder schon ins Folgejahr fällt.
	 *
	 * @param mixed $datum Geburts- oder Todestag als JJJJMMTT
	 * @param mixed $von   Beginn des Zeitraums als JJJJMMTT
	 * @param mixed $bis   Ende des Zeitraums als JJJJMMTT
	 *
	 * @return int|false Die Anzahl der Jahre, oder false wenn der Tag gar nicht
	 *                   in den Zeitraum fällt
	 */
	protected function Alter($datum, $von, $bis)
	{
		return Datum::jahrestagAlter($datum, $von, $bis);
	}
}
