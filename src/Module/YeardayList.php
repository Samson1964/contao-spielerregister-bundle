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
use Contao\PageModel;
use Schachbulle\ContaoHelperBundle\Classes\Helper as HelperBundle;
use Schachbulle\ContaoSpielerregisterBundle\Classes\SpielerregisterModul;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Spielerregister;

/**
 * Frontend-Modul "Jahrestagliste".
 *
 * Listet alle Personen auf, deren Geburts- oder Todestag in den kommenden
 * Tagen ansteht. Wie weit vorausgeschaut wird, legt die Moduleinstellung
 * "Jahrestage heute plus x Tage" fest.
 */
class YeardayList extends SpielerregisterModul
{
	/**
	 * @var string Text des Platzhalters in der Backend-Seitenstruktur
	 */
	protected $strPlatzhalter = '### SPIELERREGISTER JAHRESTAGE ###';

	/**
	 * Stellt die Daten für das Template zusammen.
	 *
	 * Der betrachtete Zeitraum kann über den Jahreswechsel hinausgehen. Deshalb
	 * wird für jede Person geprüft, ob ihr Jahrestag im laufenden oder im
	 * folgenden Jahr in den Zeitraum fällt.
	 *
	 * @return void
	 */
	protected function compile()
	{
		// Weiterleitungsseite ermitteln
		$jumpTo = PageModel::findByPk($this->spielerregister_jumpTo);

		// Zeitlichen Rahmen festlegen
		$von = date('Ymd'); // Aktuelles Datum als JJJJMMTT
		$vonjahr = substr($von, 0, 4); // Aktuelles Jahr als JJJJ
		$bis = date('Ymd', strtotime('+'.(int) $this->spielerregister_lastday.' days')); // Letztes gültiges Datum als JJJJMMTT
		$bisjahr = substr($bis, 0, 4); // Letztes gültiges Datum als JJJJ

		// Spieler laden, deren Bedeutung dem Level entspricht
		$objRegister = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister WHERE active = 1 AND (importance = 10 OR importance >= ?)')
			->execute($this->spielerregister_level);

		$daten = array();

		while ($objRegister->next())
		{
			// Geburts- und Todestag auf das laufende und das folgende Jahr legen
			$vondatum = array
			(
				substr_replace((string) $objRegister->birthday, $vonjahr, 0, 4),
				substr_replace((string) $objRegister->birthday, $bisjahr, 0, 4)
			);
			$bisdatum = array
			(
				substr_replace((string) $objRegister->deathday, $vonjahr, 0, 4),
				substr_replace((string) $objRegister->deathday, $bisjahr, 0, 4)
			);

			$gtag = (int) substr((string) $objRegister->birthday, 6, 2); // Tag für Geburtstagsprüfung
			$ttag = (int) substr((string) $objRegister->deathday, 6, 2); // Tag für Todestagprüfung

			$found = false;
			$typ = '';
			$referenztag = '';
			$sortierung = '';

			if (($vondatum[0] >= $von && $vondatum[0] <= $bis && $gtag) || ($vondatum[1] >= $von && $vondatum[1] <= $bis && $gtag))
			{
				// Geburtstag gefunden
				$typ = 'birthday';
				$referenztag = $objRegister->birthday;
				$sortierung = ($vondatum[0] >= $von && $vondatum[0] <= $bis) ? $vondatum[0] : $vondatum[1];
				$found = true;
			}

			if (($bisdatum[0] >= $von && $bisdatum[0] <= $bis && $ttag) || ($bisdatum[1] >= $von && $bisdatum[1] <= $bis && $ttag))
			{
				// Todestag gefunden. Fällt beides in den Zeitraum, gewinnt der
				// Todestag, weil er der seltenere Anlass ist.
				$typ = 'deathday';
				$sortierung = ($bisdatum[0] >= $von && $bisdatum[0] <= $bis) ? $bisdatum[0] : $bisdatum[1];
				$referenztag = $objRegister->deathday;
				$found = true;
			}

			if (!$found)
			{
				continue;
			}

			// Aktuellstes Bild laden
			$image = Spielerregister::Bilder($objRegister->multiSRC, true);

			// Daten zuweisen
			$daten[] = array
			(
				'typ'          => $typ,
				'id'           => $objRegister->id,
				'spielerlink'  => null !== $jumpTo ? $jumpTo->getFrontendUrl('/player/'.$objRegister->id) : '',
				'nachname'     => $objRegister->surname1,
				'vorname'      => $objRegister->firstname1,
				'geburtstag'   => $this->DatumToString($objRegister->birthday, 'unbekannt'),
				'verstorben'   => $objRegister->death,
				'todestag'     => $this->DatumToString($objRegister->deathday, 'unbekannt'),
				'alter'        => $this->Alter($referenztag, $von, $bis),
				'bedeutung'    => $objRegister->importance,
				'wikipedia'    => $objRegister->wikipedia,
				'kurzinfo'     => $objRegister->shortinfo,
				'linkdaten'    => Helper::getLinks($objRegister->links),
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

		// Gefundene Spieler sortieren
		$sortiert = array();

		if (\count($daten) > 0)
		{
			$sortiert = HelperBundle::sortArrayByFields
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
		$this->Template = new FrontendTemplate('spielerregister_yeardays');
		$this->Template->Ausgabe = $sortiert;
		$this->Template->Anzahl = \count($daten);
	}
}
