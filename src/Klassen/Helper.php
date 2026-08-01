<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Klassen;

use Contao\Config;
use Contao\Database;
use Contao\PageModel;
use Contao\StringUtil;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Datum;

/**
 * Sammlung von Hilfsfunktionen rund um das Spielerregister.
 *
 * Die Klasse wird sowohl aus den DCA-Dateien (als load_callback,
 * save_callback und options_callback) als auch aus den Frontend-Modulen
 * heraus benutzt und ist deshalb bewusst frei von Contao-Kontext.
 */
class Helper
{
	/**
	 * Liefert alle Personen des Registers als Auswahlliste.
	 *
	 * Wird als options_callback für das Inhaltselement "Person aus dem
	 * Spielerregister" benutzt. Der Text jeder Option besteht aus
	 * "Nachname,Vorname" und den in Klammern angehängten Lebensdaten, damit
	 * sich Namensgleiche unterscheiden lassen.
	 *
	 * Achtung: Die Abfrage liest die komplette Tabelle. Bei mehreren tausend
	 * Datensätzen dauert der Aufbau des Auswahlfeldes entsprechend lange; das
	 * ist hingenommen, weil die Liste nur im Backend gebraucht wird.
	 *
	 * @return array<int, string> Spielerregister-ID => Anzeigetext
	 */
	public static function getRegister(): array
	{
		$array = array();

		$objRegister = Database::getInstance()
			->prepare("SELECT id, surname1, firstname1, birthday, death, deathday FROM tl_spielerregister ORDER BY surname1, firstname1 ASC")
			->execute();

		while ($objRegister->next())
		{
			// Datumswerte von JJJJMMTT in die deutsche Schreibweise umwandeln
			$gebDatum = self::getDate($objRegister->birthday);
			$totDatum = $objRegister->death ? self::getDate($objRegister->deathday) : '';

			if ($gebDatum && $totDatum)
			{
				$lebensdaten = '(* '.$gebDatum.', &dagger; '.$totDatum.')';
			}
			elseif ($gebDatum && !$totDatum)
			{
				$lebensdaten = '(* '.$gebDatum.')';
			}
			elseif (!$gebDatum && $totDatum)
			{
				$lebensdaten = '(* ?, &dagger; '.$totDatum.')';
			}
			else
			{
				$lebensdaten = '';
			}

			$array[(int) $objRegister->id] = $objRegister->surname1.','.$objRegister->firstname1.' '.$lebensdaten;
		}

		return $array;
	}

	/**
	 * Wandelt einen Datumswert aus der Datenbank in die deutsche Schreibweise um.
	 *
	 * Im Spielerregister stehen Datumsangaben als Zahl JJJJMMTT, wobei die
	 * unbekannten Teile fehlen dürfen: JJJJMM bei unbekanntem Tag und JJJJ bei
	 * unbekanntem Monat. Entsprechend kommt TT.MM.JJJJ, MM.JJJJ oder JJJJ
	 * zurück. Die Methode ist als load_callback der Datumsfelder eingetragen.
	 *
	 * @param mixed $varValue Datumswert aus der Datenbank, üblicherweise eine
	 *                        Zahl; alles andere kommt unverändert zurück
	 *
	 * @return mixed Das aufbereitete Datum, ein leerer String bei "0" oder der
	 *               unveränderte Eingabewert, wenn er nicht rein numerisch ist
	 */
	public static function getDate($varValue)
	{
		if (!self::is_digit($varValue))
		{
			return $varValue;
		}

		// Gerechnet wird in Datum, damit die Formatierung nur an einer Stelle steht
		return Datum::formatiere($varValue);
	}

	/**
	 * Wandelt ein eingegebenes Datum in die Speicherform JJJJMMTT um.
	 *
	 * Gegenstück zu getDate() und als save_callback der Datumsfelder
	 * eingetragen. Fehlende Teile werden mit Nullen aufgefüllt, aus "1953" wird
	 * also 19530000. Nicht auswertbare Eingaben ergeben 0, damit in der
	 * Datenbank kein Bruchstück landet – früher wurde jede zehn Zeichen lange
	 * Eingabe zerlegt, aus "irgendwann" wurde so der Wert "wannenir".
	 *
	 * @param mixed $varValue Datum als TT.MM.JJJJ, MM.JJJJ oder JJJJ
	 *
	 * @return string|int Das Datum als JJJJMMTT, oder 0 bei unbekanntem Format
	 */
	public static function putDate($varValue)
	{
		$varValue = trim((string) $varValue);

		// Geprüft wird mit regulären Ausdrücken, damit nicht jede zehn Zeichen
		// lange Eingabe als Datum zerlegt wird
		if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $varValue, $arrTreffer))
		{
			// TT.MM.JJJJ
			$temp = $arrTreffer[3].$arrTreffer[2].$arrTreffer[1];
		}
		elseif (preg_match('/^(\d{2})\.(\d{4})$/', $varValue, $arrTreffer))
		{
			// MM.JJJJ
			$temp = $arrTreffer[2].$arrTreffer[1];
		}
		elseif (preg_match('/^\d{4}$/', $varValue))
		{
			// JJJJ
			$temp = $varValue;
		}
		else
		{
			return 0; // Kein auswertbares Datum
		}

		// Fehlende Monats- und Tagesangaben mit Nullen auffüllen
		return str_pad($temp, 8, '0');
	}

	/**
	 * Prüft, ob ein Wert ausschließlich aus Ziffern besteht.
	 *
	 * Anders als is_numeric() sind weder Vorzeichen noch Dezimaltrenner noch
	 * die Exponentialschreibweise erlaubt, weil die Datumsfelder des Registers
	 * genau das nicht enthalten dürfen.
	 *
	 * @param mixed $digit Zu prüfender Wert
	 *
	 * @return bool True bei einer Ganzzahl oder einer Zeichenkette aus Ziffern,
	 *              sonst false (auch bei true/false, Fließkommazahlen und null)
	 *
	 * @author Tim Boormans <info@directwebsolutions.nl>
	 */
	public static function is_digit($digit): bool
	{
		if (\is_int($digit))
		{
			return true; // 123
		}

		if (\is_string($digit))
		{
			return ctype_digit($digit); // "123"
		}

		// booleans, floats and others
		return false;
	}

	/**
	 * Liefert die Frontend-URL zur Detailansicht einer Person.
	 *
	 * Die Detailseite wird unter System -> Einstellungen ausgewählt
	 * (spielerregister_detailseite). Auf ihr muss das Modul "Spielerdetails"
	 * eingebunden sein, weil die ID als Auto-Item /player/<ID> angehängt wird.
	 *
	 * @param int|string $id ID des Datensatzes in tl_spielerregister
	 *
	 * @return string Die URL ohne Domain, oder ein leerer String, wenn in den
	 *                Einstellungen keine (gültige) Detailseite hinterlegt ist
	 */
	public static function getPlayerlink($id): string
	{
		$intPage = (int) Config::get('spielerregister_detailseite');

		if (!$intPage)
		{
			return '';
		}

		$objPage = PageModel::findByPk($intPage);

		if (null === $objPage)
		{
			return '';
		}

		return $objPage->getFrontendUrl('/player/'.$id);
	}

	/**
	 * Baut aus den im Register hinterlegten Weblinks die Ausgabe für die Templates.
	 *
	 * Die Links stehen als serialisiertes Array aus dem MultiColumnWizard in der
	 * Spalte "links". Ausgegeben werden nur die als aktiv markierten Einträge;
	 * ein Eintrag ohne URL erscheint als reiner Text.
	 *
	 * @param mixed $varLinks Inhalt der Spalte tl_spielerregister.links
	 *
	 * @return string Die fertigen Links in eckigen Klammern, bei fehlenden
	 *                Angaben ein leerer String
	 */
	public static function getLinks($varLinks): string
	{
		$links = StringUtil::deserialize($varLinks, true);
		$linkdaten = '';

		foreach ($links as $item)
		{
			if (!\is_array($item) || empty($item['active']))
			{
				continue;
			}

			if (!empty($item['url']))
			{
				$linkdaten .= '[<a href="'.$item['url'].'"'.(!empty($item['target']) ? ' target="_blank"' : '').'>'.(!empty($item['text']) ? $item['text'] : $item['url']).'</a>] ';
			}
			else
			{
				$linkdaten .= $item['text'] ?? '';
			}
		}

		return $linkdaten;
	}

	/**
	 * Setzt die Lebensdaten einer Person zu einer Zeile zusammen.
	 *
	 * Ergibt zum Beispiel "* 12.03.1902 Berlin; &dagger; 04.07.1975 München".
	 * Fehlende Angaben werden weggelassen; ist die Person nicht als verstorben
	 * gekennzeichnet, entfällt der zweite Teil vollständig.
	 *
	 * @param mixed $geburtstag Geburtstag als JJJJMMTT
	 * @param mixed $geburtsort Geburtsort im Klartext
	 * @param mixed $gestorben  Kennzeichen "verstorben" aus dem Register
	 * @param mixed $sterbetag  Todestag als JJJJMMTT
	 * @param mixed $sterbeort  Sterbeort im Klartext
	 *
	 * @return string Die zusammengesetzten Lebensdaten, bei fehlenden Angaben
	 *                ein leerer String
	 */
	public static function getLebensdaten($geburtstag, $geburtsort, $gestorben, $sterbetag, $sterbeort): string
	{
		$return = '';

		if ($geburtstag)
		{
			$return .= '* '.self::getDate($geburtstag);
		}

		if ($geburtsort)
		{
			$return .= ' '.$geburtsort;
		}

		if ($gestorben)
		{
			$return .= '; &dagger;';

			if ($sterbetag)
			{
				$return .= ' '.self::getDate($sterbetag);

				if ($sterbeort)
				{
					$return .= ' '.$sterbeort;
				}
			}
		}

		return $return;
	}
}
