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

class Helper 
{

	/**
	 * Liefert die Liste der registrierten Spieler für eine SELECT-Liste zurück
	 */
	public static function getRegister()
	{
		$start = microtime(true);
		$array = array();
		$objRegister = \Database::getInstance()->prepare("SELECT * FROM tl_spielerregister ORDER BY surname1,firstname1 ASC ")->execute();
		$array = array();
		while($objRegister->next())
		{
			// Datum umwandeln
			$gebDatum = self::getDate($objRegister->birthday);
			$totDatum = $objRegister->death ? self::getDate($objRegister->deathday) : '';
			if($gebDatum && $totDatum) $lebensdaten = '(* '.$gebDatum.', &dagger; '.$totDatum.')';
			elseif($gebDatum && !$totDatum) $lebensdaten = '(* '.$gebDatum.')';
			elseif(!$gebDatum && $totDatum) $lebensdaten = '(* ?, &dagger; '.$totDatum.')';
			else $lebensdaten = '';
			$array[$objRegister->id] = $objRegister->surname1 . ',' . $objRegister->firstname1 . ' ' . $lebensdaten;
		}
		$end = microtime(true);
		$laufzeit = $end - $start;
		//echo "Laufzeit: ".$laufzeit." Sekunden!";
		return $array;
	}

	/**
	 * Datumswert aus Datenbank umwandeln
	 * @param mixed
	 * @return mixed
	 */
	static function getDate($varValue)
	{
		$laenge = strlen($varValue);
		$temp = '';
		if(self::is_digit($varValue))
		{
			switch($laenge)
			{
				case 8: // JJJJMMTT
					$temp = substr($varValue,6,2).'.'.substr($varValue,4,2).'.'.substr($varValue,0,4);
					break;
				case 6: // JJJJMM
					$temp = substr($varValue,4,2).'.'.substr($varValue,0,4);
					break;
				case 4: // JJJJ
					$temp = $varValue;
					break;
				case 1: // Auf 0 prüfen
					$temp = ($varValue == '0') ? '' : $varValue;
					break;
				default: // anderer Wert
					$temp = $varValue;
			}
			return $temp;
		}
		
		return $varValue;

	}

	/**
	 * Datumswert für Datenbank umwandeln
	 * @param mixed
	 * @return mixed
	 */
	static function putDate($varValue)
	{
		$laenge = strlen(trim($varValue));
		$temp = '';
		switch($laenge)
		{
			case 10: // TT.MM.JJJJ
				$temp = substr($varValue,6,4).substr($varValue,3,2).substr($varValue,0,2);
				break;
			case 7: // MM.JJJJ
				$temp = substr($varValue,3,4).substr($varValue,0,2);
				break;
			case 4: // JJJJ
				$temp = $varValue;
				break;
			default: // anderer Wert
				$temp = 0;
		}

		if($temp) $temp = str_pad($temp, 8, '0'); // Mit Nullen auffüllen
		return $temp;
	}

	/**
	 * Check input for existing only of digits (numbers)
	 * @author Tim Boormans <info@directwebsolutions.nl>
	 * @param $digit
	 * @return bool
	 */
	static function is_digit($digit) 
	{
		if(is_int($digit)) 
		{
			return true; // 123 
		} 
		elseif(is_string($digit)) 
		{
			return ctype_digit($digit); // true "123"
		} 
		else 
		{
			// booleans, floats and others
			return false;
		}
	}

	/**
	 * Gibt die URL zum Detaileintrag eines Spielers zurück
	 * @param $id       ID des Spielers in tl_spielerregister
	 * @return string
	 */
	static function getPlayerlink($id)
	{
		// Weiterleitungsseite ermitteln, $this->spielerregister_jumpTo steht aber nur im Modul!
		//$jumpTo = \PageModel::findByPk($this->spielerregister_jumpTo);

		// z.Z. hardcodiert
		return 'person/player/'.$id.'.html';
		
	}

}
