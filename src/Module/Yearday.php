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
use Schachbulle\ContaoSpielerregisterBundle\Classes\Bilder;
use Schachbulle\ContaoSpielerregisterBundle\Classes\SpielerregisterModul;

/**
 * Frontend-Modul "Aktueller Jahrestag".
 *
 * Zeigt den nächsten anstehenden Jahrestag als Einzelmeldung an, gedacht für
 * eine Box in der Seitenspalte. Betrachtet werden die kommenden 60 Tage;
 * Personen, bei denen "keine Hervorhebung" gesetzt ist, bleiben außen vor.
 */
class Yearday extends SpielerregisterModul
{
	/**
	 * @var string Text des Platzhalters in der Backend-Seitenstruktur
	 */
	protected $strPlatzhalter = '### SPIELERREGISTER JAHRESTAG ###';

	/**
	 * Stellt die Daten für das Template zusammen.
	 *
	 * Gesammelt werden alle Jahrestage der nächsten 60 Tage; ausgegeben wird
	 * nach der Sortierung nur der zeitlich nächste. Die Gesamtzahl steht dem
	 * Template als "Anzahl" zur Verfügung.
	 *
	 * @return void
	 */
	protected function compile()
	{
		// Zeitlichen Rahmen festlegen
		$von = date('Ymd');
		$vonjahr = substr($von, 0, 4);
		$bis = date('Ymd', strtotime('+60 day'));
		$bisjahr = substr($bis, 0, 4);

		$objRegister = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister WHERE active = 1 AND nohighlighting = 0 AND (importance = 10 OR importance >= ?)')
			->execute($this->spielerregister_level);

		// Template-Objekt anlegen
		$this->Template = new FrontendTemplate('spielerregister_yearday');

		// Link auf die Übersichtsseite ("Mehr Jahrestage"); ohne eingestellte
		// Weiterleitungsseite entfällt der Link im Template
		$jumpTo = PageModel::findByPk($this->spielerregister_jumpTo);
		$this->Template->mehrlink = null !== $jumpTo ? $jumpTo->getFrontendUrl() : '';

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

			$tag = (int) substr((string) $objRegister->birthday, 6, 2);

			if (($vondatum[0] >= $von && $vondatum[0] <= $bis && $tag) || ($vondatum[1] >= $von && $vondatum[1] <= $bis && $tag))
			{
				// Geburtstag merken
				$sortierung = ($vondatum[0] >= $von && $vondatum[0] <= $bis) ? $vondatum[0] : $vondatum[1];
				$daten[] = $this->erzeugeEintrag($objRegister, 'birthday', $objRegister->birthday, $von, $bis, $sortierung);
			}

			$tag = (int) substr((string) $objRegister->deathday, 6, 2);

			if (($bisdatum[0] >= $von && $bisdatum[0] <= $bis && $tag) || ($bisdatum[1] >= $von && $bisdatum[1] <= $bis && $tag))
			{
				// Todestag merken
				$sortierung = ($bisdatum[0] >= $von && $bisdatum[0] <= $bis) ? $bisdatum[0] : $bisdatum[1];
				$daten[] = $this->erzeugeEintrag($objRegister, 'deathday', $objRegister->deathday, $von, $bis, $sortierung);
			}
		}

		// Daten sortieren, damit der nächste Jahrestag vorn steht
		if ($daten)
		{
			$daten = HelperBundle::sortArrayByFields
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

		// Vom Template gelesene Variablen vorbelegen und den ersten Eintrag ausgeben
		$erster = $daten[0] ?? array();

		foreach (array('sortierung', 'monatstag', 'id', 'typ', 'nachname', 'vorname', 'geburtstag', 'verstorben', 'todestag', 'alter', 'bedeutung', 'wikipedia', 'kurzinfo', 'bild') as $strFeld)
		{
			$this->Template->$strFeld = $erster[$strFeld] ?? '';
		}

		$this->Template->Anzahl = \count($daten);
	}

	/**
	 * Baut den Datensatz eines Jahrestags für die Ausgabe zusammen.
	 *
	 * @param object     $objRegister Datensatz der Person aus tl_spielerregister
	 * @param string     $strTyp      'birthday' oder 'deathday'
	 * @param mixed      $varStichtag Das Datum, auf das sich der Jahrestag bezieht
	 * @param string     $von         Beginn des Zeitraums als JJJJMMTT
	 * @param string     $bis         Ende des Zeitraums als JJJJMMTT
	 * @param string     $sortierung  Datum des Jahrestags im Zeitraum als JJJJMMTT
	 *
	 * @return array Die Daten einer Zeile für das Template
	 */
	protected function erzeugeEintrag($objRegister, string $strTyp, $varStichtag, string $von, string $bis, string $sortierung): array
	{
		return array
		(
			'typ'         => $strTyp,
			'id'          => $objRegister->id,
			'nachname'    => $objRegister->surname1,
			'vorname'     => $objRegister->firstname1,
			'geburtstag'  => $this->DatumToString($objRegister->birthday, 'unbekannt'),
			'verstorben'  => $objRegister->death,
			'todestag'    => $this->DatumToString($objRegister->deathday, 'unbekannt'),
			'alter'       => $this->Alter($varStichtag, $von, $bis),
			'bedeutung'   => $objRegister->importance,
			'wikipedia'   => $objRegister->wikipedia,
			'kurzinfo'    => $objRegister->shortinfo,
			'sortierung'  => $sortierung,
			'monatstag'   => $this->LadeTag($sortierung),
			'bild'        => $this->ladeBild($objRegister->id)
		);
	}

	/**
	 * Lädt das jüngste Foto einer Person als Vorschaubild.
	 *
	 * Gelesen wird tl_spielerregister_images, weil dort das Aufnahmedatum
	 * gepflegt ist und sich damit das aktuellste Foto bestimmen lässt.
	 *
	 * @param int|string $intPid ID der Person in tl_spielerregister
	 *
	 * @return string URL des Vorschaubildes, oder ein leerer String, wenn kein
	 *                Foto hinterlegt ist
	 */
	protected function ladeBild($intPid): string
	{
		$objImage = Database::getInstance()
			->prepare('SELECT * FROM tl_spielerregister_images WHERE pid = ? ORDER BY imagedate DESC')
			->limit(1)
			->execute($intPid);

		if (!$objImage->numRows)
		{
			return '';
		}

		return Bilder::vorschaubildAusUuid($objImage->singleSRC, 120, 120, 'proportional');
	}
}
