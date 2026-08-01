<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Classes;

/**
 * Rechnen mit den Datumsangaben des Spielerregisters.
 *
 * Im Register stehen Datumsangaben als Zahl JJJJMMTT. Unbekannte Bestandteile
 * sind mit Nullen belegt: 19000000 heißt "irgendwann 1900", 19410100 heißt
 * "im Januar 1941". Genau daran scheitern die Datumsfunktionen von PHP, deshalb
 * rechnet diese Klasse selbst.
 *
 * Die Klasse kommt bewusst ohne Contao aus, damit sie sich mit Unit-Tests
 * prüfen lässt.
 */
class Datum
{
	/**
	 * @var array<int, string> Monatsnamen, Index 1 = Januar
	 */
	public const MONATSNAMEN = array('', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');

	/**
	 * Formatiert ein Datum für die Ausgabe.
	 *
	 * Fehlt der Tag, wird er weggelassen, fehlt auch der Monat, bleibt nur die
	 * Jahreszahl übrig. Aus 19410111 wird also "11.01.1941", aus 19410100 wird
	 * "01.1941" und aus 19410000 wird "1941".
	 *
	 * @param mixed  $datum   Datum als JJJJMMTT
	 * @param string $strLeer Rückgabewert, wenn im Datum nichts Verwertbares steht
	 *
	 * @return string Das formatierte Datum, sonst $strLeer
	 */
	public static function formatiere($datum, string $strLeer = ''): string
	{
		$arrTeile = self::zerlege($datum);

		if (null === $arrTeile)
		{
			return $strLeer;
		}

		$temp = '';

		if ($arrTeile['tag'])
		{
			$temp .= sprintf('%02d', $arrTeile['tag']).'.';
		}

		if ($arrTeile['monat'])
		{
			$temp .= sprintf('%02d', $arrTeile['monat']).'.';
		}

		if ($arrTeile['jahr'])
		{
			$temp .= sprintf('%04d', $arrTeile['jahr']);
		}

		return $temp !== '' ? $temp : $strLeer;
	}

	/**
	 * Liefert Tag und ausgeschriebenen Monat, also zum Beispiel "4. Juli".
	 *
	 * @param mixed $datum Datum als JJJJMMTT
	 *
	 * @return string Tag und Monatsname; fehlt der Monat, bleibt der Name leer
	 */
	public static function monatstag($datum): string
	{
		$datum = (string) $datum;
		$monat = (int) substr($datum, 4, 2);

		return (int) substr($datum, 6, 2).'. '.(self::MONATSNAMEN[$monat] ?? '');
	}

	/**
	 * Liefert die Jahreszahl eines Datums.
	 *
	 * @param mixed $datum Datum als JJJJMMTT
	 *
	 * @return string Die vierstellige Jahreszahl, sonst ein leerer String
	 */
	public static function jahr($datum): string
	{
		return substr((string) $datum, 0, 4);
	}

	/**
	 * Zerlegt ein Datum in Tag, Monat und Jahr.
	 *
	 * Erkannt werden die Schreibweisen JJJJMMTT, JJJJMM, JJJJ und TT.MM.JJJJ.
	 * Anders als bei checkdate() dürfen Tag und Monat 0 sein, weil das im
	 * Register für "unbekannt" steht.
	 *
	 * @param mixed $datum Das zu zerlegende Datum
	 *
	 * @return array<string, int>|null Die Bestandteile mit den Schlüsseln tag,
	 *                                 monat und jahr, oder null bei unbekanntem
	 *                                 Format oder fehlender Jahreszahl
	 */
	public static function zerlege($datum): ?array
	{
		$datum = trim((string) $datum);

		switch (\strlen($datum))
		{
			case 8: // JJJJMMTT
				$tag = (int) substr($datum, 6, 2);
				$monat = (int) substr($datum, 4, 2);
				$jahr = (int) substr($datum, 0, 4);
				break;

			case 6: // JJJJMM
				$tag = 0;
				$monat = (int) substr($datum, 4, 2);
				$jahr = (int) substr($datum, 0, 4);
				break;

			case 4: // JJJJ
				$tag = 0;
				$monat = 0;
				$jahr = (int) $datum;
				break;

			case 10: // TT.MM.JJJJ
				$tag = (int) substr($datum, 0, 2);
				$monat = (int) substr($datum, 3, 2);
				$jahr = (int) substr($datum, 6, 4);
				break;

			default: // anderer Wert
				return null;
		}

		if (!$jahr)
		{
			return null;
		}

		return array('tag' => $tag, 'monat' => $monat, 'jahr' => $jahr);
	}

	/**
	 * Berechnet, der wievielte Jahrestag im betrachteten Zeitraum ansteht.
	 *
	 * Der Zeitraum kann über den Jahreswechsel hinausgehen. Deshalb wird anhand
	 * des Monats entschieden, ob der Jahrestag noch ins Anfangsjahr oder schon
	 * ins Folgejahr fällt.
	 *
	 * @param mixed $datum Geburts- oder Todestag als JJJJMMTT
	 * @param mixed $von   Beginn des Zeitraums als JJJJMMTT
	 * @param mixed $bis   Ende des Zeitraums als JJJJMMTT
	 *
	 * @return int|false Die Anzahl der Jahre, oder false wenn der Tag gar nicht
	 *                   in den Zeitraum fällt
	 */
	public static function jahrestagAlter($datum, $von, $bis)
	{
		$datum = (string) $datum;

		$jahr = (int) substr($datum, 0, 4);
		$vonjahr = (int) substr((string) $von, 0, 4);
		$bisjahr = (int) substr((string) $bis, 0, 4);

		$monat = substr($datum, 4);
		$vonmonat = substr((string) $von, 4);
		$bismonat = substr((string) $bis, 4);

		if ($vonjahr === $bisjahr)
		{
			// Gleiches Jahr, Berechnung ganz einfach
			return $vonjahr - $jahr;
		}

		if ($monat < $vonmonat && $monat <= $bismonat)
		{
			// Monat zu klein, muß also Folgejahr sein
			return $bisjahr - $jahr;
		}

		if ($monat > $bismonat && $monat >= $vonmonat)
		{
			// Monat zu groß, muß also letztes Jahr sein
			return $vonjahr - $jahr;
		}

		return false;
	}

	/**
	 * Berechnet das Alter zwischen zwei Datumsangaben in vollen Jahren.
	 *
	 * Gebraucht wird das für das Sterbealter. Unvollständige Datumsangaben –
	 * im Register durchaus üblich – ergeben kein gültiges Datum und damit auch
	 * kein Alter; die aufrufende Stelle gibt dann ein Fragezeichen aus.
	 *
	 * @param mixed $start Startdatum, üblicherweise der Geburtstag
	 * @param mixed $ende  Endedatum, üblicherweise der Todestag
	 *
	 * @return int|false Das Alter in Jahren, oder false wenn eine der beiden
	 *                   Angaben kein vollständiges, gültiges Datum ist
	 */
	public static function alterZwischen($start, $ende)
	{
		$arrStart = self::zerlege($start);
		$arrEnde = self::zerlege($ende);

		if (null === $arrStart || null === $arrEnde)
		{
			return false;
		}

		// Ohne Tag und Monat lässt sich das Alter nicht auf das Jahr genau sagen
		if (!checkdate($arrStart['monat'], $arrStart['tag'], $arrStart['jahr']) || !checkdate($arrEnde['monat'], $arrEnde['tag'], $arrEnde['jahr']))
		{
			return false;
		}

		$intAlter = $arrEnde['jahr'] - $arrStart['jahr'];

		// Geburtstag im Zieljahr noch nicht erreicht
		if ($arrStart['monat'] > $arrEnde['monat'] || ($arrStart['monat'] === $arrEnde['monat'] && $arrStart['tag'] > $arrEnde['tag']))
		{
			$intAlter--;
		}

		return $intAlter;
	}

	/**
	 * Wandelt ein Datum in die ISO-Schreibweise JJJJ-MM-TT um.
	 *
	 * Fehlende Angaben werden ergänzt: ohne Monat der Juni, ohne Tag der
	 * Monatserste. Ohne diese Annahme ließe sich für die vielen unvollständigen
	 * Datumsangaben des Registers gar keine Zeitspanne ausweisen; die Angabe
	 * ist dann näherungsweise.
	 *
	 * @param mixed $datum Datum als JJJJMMTT, JJJJMM, JJJJ oder TT.MM.JJJJ
	 *
	 * @return string|null Das Datum als JJJJ-MM-TT, oder null bei unbekanntem Format
	 */
	public static function isoDatum($datum): ?string
	{
		$arrTeile = self::zerlege($datum);

		if (null === $arrTeile)
		{
			return null;
		}

		$monat = $arrTeile['monat'] ?: 6;
		$tag = $arrTeile['tag'] ?: 1;

		if (!checkdate($monat, $tag, $arrTeile['jahr']))
		{
			return null;
		}

		return sprintf('%04d-%02d-%02d', $arrTeile['jahr'], $monat, $tag);
	}

	/**
	 * Berechnet die Zeitspanne zwischen zwei Datumsangaben.
	 *
	 * @param mixed $start Startdatum, üblicherweise der Geburtstag
	 * @param mixed $ende  Endedatum, üblicherweise das Datum einer Titelverleihung
	 *
	 * @return \DateInterval|null Die Zeitspanne, oder null wenn eines der beiden
	 *                            Daten kein auswertbares Format hat
	 */
	public static function zeitspanne($start, $ende): ?\DateInterval
	{
		$strStart = self::isoDatum($start);
		$strEnde = self::isoDatum($ende);

		if (null === $strStart || null === $strEnde)
		{
			return null;
		}

		try
		{
			$date1 = new \DateTime($strStart);
			$date2 = new \DateTime($strEnde);
		}
		catch (\Exception $e)
		{
			return null;
		}

		return $date1->diff($date2);
	}
}
