<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   fh-counter
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

/**
 * Benutzerdefinierten Namespace festlegen, damit die Klasse ersetzt werden kann
 */
namespace Schachbulle\ContaoSpielerregisterBundle\Module;

/**
 * Class CounterRegister
 *
 * @copyright  Frank Hoppe 2014
 * @author     Frank Hoppe
 *
 * Basisklasse vom FH-Counter
 * Erledigt die Zählung der jeweiligen Contenttypen und schreibt die Zählerwerte in $GLOBALS
 */
class YeardayList extends \Module
{

	var $monatsname = array("","Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_spielerregister');

			$objTemplate->wildcard = '### SPIELERREGISTER ###';
			$objTemplate->title = $this->name;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}

		return parent::generate(); // Weitermachen mit dem Modul
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		global $objPage;

		// Weiterleitungsseite ermitteln
		$jumpTo = \PageModel::findByPk($this->spielerregister_jumpTo);

		// Zeitlichen Rahmen festlegen
		$von = date("Ymd"); // Aktuelles Datum als JJJJMMTT
		$vonjahr = substr($von, 0, 4); // Aktuelles Jahr als JJJJ
		$bis = date("Ymd", strtotime("+" . $this->spielerregister_lastday. " days")); // Letztes gültiges Datum als JJJJMMTT
		$bisjahr = substr($bis, 0, 4); // Letztes gültiges Datum als JJJJ

		// Spieler laden, deren Bedeutung dem Level entspricht
		$objRegister = \Database::getInstance()->prepare('SELECT * FROM tl_spielerregister WHERE active = 1 AND (importance = 10 OR importance >= ?)')
		                                       ->execute($this->spielerregister_level);

		$daten = array();
		if($objRegister->numRows)
		{
			// Datensätze der Reihe nach anhand der Kriterien durchsuchen
			while($objRegister->next())
			{

				$vondatum[0] = substr_replace($objRegister->birthday,$vonjahr,0,4);
				$vondatum[1] = substr_replace($objRegister->birthday,$bisjahr,0,4);
				$bisdatum[0] = substr_replace($objRegister->deathday,$vonjahr,0,4);
				$bisdatum[1] = substr_replace($objRegister->deathday,$bisjahr,0,4);
				$gtag = (int)substr($objRegister->birthday,6,2); // Tag für Geburtstagsprüfung
				$ttag = (int)substr($objRegister->deathday,6,2); // Tag für Todestagprüfung
				$found = false;

				if(($vondatum[0] >= $von && $vondatum[0] <= $bis && $gtag) || ($vondatum[1] >= $von && $vondatum[1] <= $bis && $gtag))
				{
					// Geburtstag gefunden
					$typ = 'birthday';
					$referenztag = $objRegister->birthday;
					$sortierung = ($vondatum[0] >= $von && $vondatum[0] <= $bis) ? $vondatum[0] : $vondatum[1];
					$found = true;
				}
				if(($bisdatum[0] >= $von && $bisdatum[0] <= $bis && $ttag) || ($bisdatum[1] >= $von && $bisdatum[1] <= $bis && $ttag))
				{
					// Todestag gefunden
					$typ = 'deathday';
					$sortierung = ($bisdatum[0] >= $von && $bisdatum[0] <= $bis) ? $bisdatum[0] : $bisdatum[1];
					$referenztag = $objRegister->deathday;
					$found = true;
				}

				if($found)
				{
					// Spieler merken

					// Daten vorbereiten
					$image = \Schachbulle\ContaoSpielerregisterBundle\Klassen\Spielerregister::Bilder($objRegister->multiSRC, true); // Aktuellstes Bild laden

					// Links bauen
					$links = (array)unserialize($objRegister->links);
					$linkdaten = '';
					if(is_array($links) && count($links) > 0)
					{
						foreach($links as $item)
						{
							if(isset($item['active']) && $item['active'])
							{
								$linkdaten .= $item['url'] ? '[<a href="'.$item['url'].'"'.($item['target'] ? ' target="_blank"' : '').'>'.($item['text'] ? $item['text'] : $item['url']).'</a>] ' : $item['text'];
							}
						}
					}

					// Daten zuweisen
					$daten[] = array
					(
						'typ'          => $typ,
						'id'           => $objRegister->id,
						'spielerlink'  => $jumpTo->getFrontendUrl('/player/' . $objRegister->id),
						'nachname'     => $objRegister->surname1,
						'vorname'      => $objRegister->firstname1,
						'geburtstag'   => $this->DatumToString($objRegister->birthday),
						'verstorben'   => $objRegister->death,
						'todestag'     => $this->DatumToString($objRegister->deathday),
						'alter'        => $this->Alter($referenztag, $von, $bis),
						'bedeutung'    => $objRegister->importance,
						'wikipedia'    => $objRegister->wikipedia,
						'kurzinfo'     => $objRegister->shortinfo,
						'linkdaten'    => $linkdaten,
						'sortierung'   => $sortierung,
						'monatstag'    => $this->LadeTag($sortierung),
						'image'        => $image ? $image['image'] : false,
						'imageSize'    => $image ? $image['imageSize'] : false,
						'imageTitle'   => $image ? $image['imageTitle'] : false,
						'imageAlt'     => $image ? $image['imageAlt'] : false,
						'imageCaption' => $image ? $image['imageCaption'] : false,
						'thumbnail'    => $image ? $image['thumbnail'] : false
					);
				}
			}
		}

		// Gefundene Spieler sortieren
		$sortiert = array();
		if(count($daten) > 0)
		{
			$sortiert = \Schachbulle\ContaoHelperBundle\Classes\Helper::sortArrayByFields
			(
				$daten,
				array
				(
					'sortierung' => SORT_ASC,
					'monatstag'  => SORT_ASC,
					'id'         => SORT_ASC
				)
			);
		}

		// Template-Objekt anlegen und Daten zuweisen
		$this->Template = new \FrontendTemplate('spielerregister_yeardays');
		$this->Template->Ausgabe = $sortiert;
		$this->Template->Anzahl = count($daten);

	}

	protected function LadeTag($datum) {

		return (0+substr($datum,6,2)).". ".$this->monatsname[0+substr($datum,4,2)];

	}

	protected function LadeJahr($datum) {

		return substr($datum,0,4);

	}

	protected function DatumToString($datum) {

		$temp = '';
		$tag = (int)substr($datum,6,2) + 0;
		$monat = (int)substr($datum,4,2) + 0;
		$jahr = (int)substr($datum,0,4) + 0;

		if($tag) $temp .= sprintf('%02d', $tag) . '.';
		if($monat) $temp .= sprintf('%02d', $monat) . '.';
		if($jahr) $temp .= sprintf('%04d', $jahr);

		if(!$temp) $temp = 'unbekannt';
		return $temp;

	}

	protected function Alter($datum, $von, $bis)
	{

		$jahr = substr($datum,0,4);
		$vonjahr = substr($von,0,4);
		$bisjahr = substr($bis,0,4);

		$monat = substr($datum,4);
		$vonmonat = substr($von,4);
		$bismonat = substr($bis,4);

		if($vonjahr == $bisjahr) {
			// Gleiches Jahr, Berechnung ganz einfach
			return $vonjahr - $jahr;
		}
		elseif($monat < $vonmonat && $monat <= $bismonat) {
			// Monat zu klein, muß also Folgejahr sein
			return $bisjahr - $jahr;
		}
		elseif($monat > $bismonat && $monat >= $vonmonat) {
			// Monat zu groß, muß also letztes Jahr sein
			return $vonjahr - $jahr;
		}
		else return false;

	}

}
