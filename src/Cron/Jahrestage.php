<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Cron;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Email;
use Contao\System;
use Psr\Log\LoggerInterface;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Datum;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Cronjob "Jahrestags-Newsletter".
 *
 * Verschickt an die Empfänger eines Newsletter-Verteilers eine Übersicht der
 * anstehenden Geburts- und Todestage: heute, morgen, in einer Woche und in
 * zwei Wochen.
 *
 * Der Cronjob ersetzt das frühere Aufrufskript
 * public/jahrestage.php, das seinerseits system/initialize.php eingebunden hat.
 * Diesen Weg gibt es in Contao 5 nicht mehr; der externe Cron-Aufruf per URL
 * entfällt damit ersatzlos.
 *
 * Alle Einstellungen stehen im Backend unter System -> Einstellungen im
 * Abschnitt "Spielerregister".
 *
 * ACHTUNG: Dieser Cronjob verschickt E-Mails an echte Empfänger. Er tut das
 * erst, wenn die Einstellung "Jahrestags-Newsletter automatisch versenden"
 * (spielerregister_cron) gesetzt ist.
 */
class Jahrestage
{
	/**
	 * @var ContaoFramework Wird gebraucht, um die Legacy-Klassen zu benutzen
	 */
	private $framework;

	/**
	 * @var LoggerInterface|null Protokolliert Ergebnis und Abbruchgründe
	 */
	private $logger;

	/**
	 * @param ContaoFramework      $framework Contao-Framework für die Legacy-Klassen
	 * @param LoggerInterface|null $logger    Optionales Protokoll (contao.cron)
	 */
	public function __construct(ContaoFramework $framework, ?LoggerInterface $logger = null)
	{
		$this->framework = $framework;
		$this->logger = $logger;
	}

	/**
	 * Führt den Versand durch.
	 *
	 * Je Durchgang werden höchstens so viele E-Mails verschickt, wie unter
	 * "Anzahl Mails" eingestellt ist; wer heute schon eine bekommen hat, wird
	 * übersprungen. Ist eine Wartezeit eingestellt, wird der Durchgang so lange
	 * wiederholt, bis alle Empfänger versorgt sind.
	 *
	 * @return void
	 */
	public function __invoke(): void
	{
		// Contao-Framework initialisieren (Legacy-Klassen Config, Database, Email)
		$this->framework->initialize();

		if (!Config::get('spielerregister_cron'))
		{
			return; // Automatikversand ist nicht eingeschaltet
		}

		$intChannel = (int) Config::get('spielerregister_newsletter');

		if (!$intChannel)
		{
			$this->log('error', 'Jahrestags-Newsletter übersprungen: Es ist kein Newsletter-Verteiler ausgewählt.');

			return;
		}

		$strAbsender = trim((string) Config::get('spielerregister_mailAbsender'));

		if ('' === $strAbsender)
		{
			$this->log('error', 'Jahrestags-Newsletter übersprungen: In den Einstellungen ist keine Absenderadresse hinterlegt.');

			return;
		}

		$intZyklus = (int) Config::get('spielerregister_zyklus');
		$intWartezeit = (int) Config::get('spielerregister_wartezeit');
		$intHeute = mktime(0, 0, 0); // Heute 0:00:00 Uhr
		$intGesamt = 0;

		do
		{
			$arrEmpfaenger = $this->getEmpfaenger($intChannel, (int) $intHeute, $intZyklus);

			if (!$arrEmpfaenger)
			{
				break; // Keine Empfänger mehr offen
			}

			$strContent = $this->erzeugeInhalt();

			if ('' === $strContent)
			{
				break; // Es stehen keine Jahrestage an
			}

			$intGesamt += $this->versende($arrEmpfaenger, $strContent, $strAbsender, $intChannel);

			// Ohne Wartezeit bleibt es bei einem Durchgang je Cron-Lauf
			if ($intWartezeit < 1)
			{
				break;
			}

			sleep($intWartezeit);
		}
		while (true);

		if ($intGesamt > 0)
		{
			$this->log('info', sprintf('Jahrestags-Newsletter an %d Empfänger versendet', $intGesamt));
		}
	}

	/**
	 * Liest die Empfänger, die heute noch keine E-Mail bekommen haben.
	 *
	 * @param int $intChannel ID des Newsletter-Verteilers
	 * @param int $intHeute   Zeitstempel von heute 0:00 Uhr
	 * @param int $intZyklus  Höchstzahl der E-Mails je Durchgang; 0 = alle
	 *
	 * @return array<int, string> Die E-Mail-Adressen der Empfänger
	 */
	private function getEmpfaenger(int $intChannel, int $intHeute, int $intZyklus): array
	{
		$objDatabase = Database::getInstance()
			->prepare("SELECT email FROM tl_newsletter_recipients WHERE active = ? AND pid = ? AND spielerregister_mailTime < ?");

		if ($intZyklus > 0)
		{
			$objDatabase->limit($intZyklus);
		}

		$objNewsletter = $objDatabase->execute(1, $intChannel, $intHeute);

		$arrEmpfaenger = array();

		while ($objNewsletter->next())
		{
			$arrEmpfaenger[] = $objNewsletter->email;
		}

		return $arrEmpfaenger;
	}

	/**
	 * Baut den Inhalt der E-Mail aus den anstehenden Jahrestagen.
	 *
	 * Betrachtet werden vier Stichtage: heute, morgen, in einer Woche und in
	 * zwei Wochen. Verglichen wird nur Monat und Tag, das Jahr ergibt sich aus
	 * dem jeweiligen Stichtag – so funktioniert der Vergleich auch über den
	 * Jahreswechsel hinweg.
	 *
	 * @return string Der fertige HTML-Text, oder ein leerer String wenn an
	 *                keinem der Stichtage ein Jahrestag ansteht
	 */
	private function erzeugeInhalt(): string
	{
		$zieltext = array('Heute', 'Morgen', 'In 1 Woche', 'In 2 Wochen');
		$zielzeit = array(date('md'), date('md', strtotime('+1 day')), date('md', strtotime('+1 week')), date('md', strtotime('+2 week')));
		$zieljahr = array(date('Y'), date('Y', strtotime('+1 day')), date('Y', strtotime('+1 week')), date('Y', strtotime('+2 week')));

		$ausgabe = array('', '', '', '');
		$strBasis = rtrim((string) Config::get('spielerregister_mailUrl'), '/');

		$objPlayers = Database::getInstance()
			->prepare("SELECT * FROM tl_spielerregister WHERE active = 1")
			->execute();

		while ($objPlayers->next())
		{
			$geburtstag = substr((string) $objPlayers->birthday, 4, 4);
			$todestag = $objPlayers->death ? substr((string) $objPlayers->deathday, 4, 4) : false;

			for ($x = 0; $x < \count($zielzeit); $x++)
			{
				$blnGeburtstag = ($geburtstag && $geburtstag === $zielzeit[$x]);
				$blnTodestag = ($todestag && $todestag === $zielzeit[$x]);

				if (!$blnGeburtstag && !$blnTodestag)
				{
					continue;
				}

				// Der Todestag hat Vorrang, wenn beides auf denselben Tag fällt
				if ($blnTodestag)
				{
					$alter = (int) $zieljahr[$x] - (int) substr((string) $objPlayers->deathday, 0, 4);
					$tagestext = 'Todestag';
				}
				else
				{
					$alter = (int) $zieljahr[$x] - (int) substr((string) $objPlayers->birthday, 0, 4);
					$tagestext = 'Geburtstag';
				}

				// Alter zum Zeitpunkt des Todes berechnen
				$todtext = '';

				if ($objPlayers->death)
				{
					$todesalter = Datum::alterZwischen($objPlayers->birthday, $objPlayers->deathday);
					$todtext = false !== $todesalter ? ' (&dagger; '.$todesalter.')' : ' (&dagger; ?)';
				}

				// Spielername ausgeben, wenn möglich mit Link auf die Detailseite
				$strName = trim($objPlayers->firstname1.' '.$objPlayers->surname1);
				$strLink = Helper::getPlayerlink($objPlayers->id);

				if ('' !== $strLink)
				{
					$strName = '<a href="'.$strBasis.$strLink.'">'.$strName.'</a>';
				}

				$ausgabe[$x] .= '<p><b>'.$alter.'. '.$tagestext.' von '.$strName.$todtext.'</b></p>';
				$ausgabe[$x] .= '<div style="padding-left:10px;">';

				// Kurzinfo ausgeben
				if ($objPlayers->shortinfo)
				{
					$ausgabe[$x] .= $objPlayers->shortinfo;
				}

				// Ehrungen ausgeben
				$ausgabe[$x] .= self::getEhrungen($objPlayers);
				$ausgabe[$x] .= '</div>';
			}
		}

		$content = '';

		for ($x = 0; $x < \count($ausgabe); $x++)
		{
			if (!$ausgabe[$x])
			{
				continue;
			}

			$tag = substr($zielzeit[$x], 2, 2).'.'.substr($zielzeit[$x], 0, 2).'.'.$zieljahr[$x];
			$content .= '<h2>'.$zieltext[$x].' ('.$tag.')</h2><p>'.$ausgabe[$x].'</p>';
		}

		if ('' === $content)
		{
			return '';
		}

		// Insert-Tags ersetzen und Rahmentext ergänzen
		$content = System::getContainer()->get('contao.insert_tag.parser')->replace($content);

		$strKopf = (string) Config::get('spielerregister_mailKopf');
		$strFuss = (string) Config::get('spielerregister_mailFuss');

		return ($strKopf ?: '<h1>Jahrestage</h1><p>Folgende Jahrestage stehen an:</p>').$content.$strFuss;
	}

	/**
	 * Verschickt den Newsletter an die übergebenen Empfänger.
	 *
	 * Jede E-Mail geht einzeln raus, damit sich der Platzhalter ##email## durch
	 * die jeweilige Adresse ersetzen lässt – so funktioniert der Abmeldelink in
	 * der Fußzeile. Nach dem Versand wird die Versandzeit am Empfänger
	 * vermerkt, damit er an diesem Tag keine zweite E-Mail bekommt.
	 *
	 * @param array<int, string> $arrEmpfaenger E-Mail-Adressen
	 * @param string             $strContent    Fertiger HTML-Text
	 * @param string             $strAbsender   Absenderadresse
	 * @param int                $intChannel    ID des Newsletter-Verteilers
	 *
	 * @return int Anzahl der verschickten E-Mails
	 */
	private function versende(array $arrEmpfaenger, string $strContent, string $strAbsender, int $intChannel): int
	{
		$strName = trim((string) Config::get('spielerregister_mailAbsendername'));
		$strBetreff = trim((string) Config::get('spielerregister_mailBetreff')) ?: 'Jahrestage im Spielerregister';
		$strBasis = rtrim((string) Config::get('spielerregister_mailUrl'), '/');

		$intAnzahl = 0;

		foreach ($arrEmpfaenger as $adresse)
		{
			$text = str_replace('##email##', $adresse, $strContent);

			if ('' !== $strBasis)
			{
				// Domain zu Datei- und Weblinks hinzufügen, sonst laufen die
				// Links im E-Mail-Programm ins Leere
				$text = str_replace(array('"files/', '"index.php/'), array('"'.$strBasis.'/files/', '"'.$strBasis.'/'), $text);
			}

			// Fehlerbehandlung je Empfänger: Hier geht eine eigene Nachricht
			// an jede Adresse, deshalb darf eine unbrauchbare Adresse nur
			// diese eine Mail kosten. Ohne die Klammer brach der Cronjob ab
			// und alle folgenden Empfänger gingen leer aus — unbemerkt, weil
			// niemand dabei zusieht
			try
			{
				$objEmail = new Email();
				$objEmail->from = $strAbsender;
				$objEmail->charset = 'utf-8';
				$objEmail->subject = $strBetreff;
				$objEmail->html = $text;

				if ('' !== $strName)
				{
					$objEmail->fromName = $strName;
				}

				$objEmail->sendTo($adresse);
			}
			catch (\Throwable $e)
			{
				$this->log('error', sprintf('Jahrestage-Mail an %s konnte nicht verschickt werden: %s', $adresse, $e->getMessage()));

				continue;
			}

			// Versandzeit in Datenbank eintragen
			Database::getInstance()
				->prepare("UPDATE tl_newsletter_recipients SET spielerregister_mailTime=? WHERE pid=? AND email=?")
				->execute(time(), $intChannel, $adresse);

			$intAnzahl++;
		}

		return $intAnzahl;
	}

	/**
	 * Listet die Ehrungen einer Person als kursive Zeilen auf.
	 *
	 * @param object $objPlayer Datensatz aus tl_spielerregister
	 *
	 * @return string Die Ehrungen, bei keiner Ehrung ein leerer String
	 */
	private static function getEhrungen($objPlayer): string
	{
		$arrEhrungen = array
		(
			'honorpresident'   => 'Ehrenpräsident',
			'honormember'      => 'Ehrenmitglied',
			'honorgoldpin'     => 'Goldene Ehrennadel',
			'honorsilverpin'   => 'Silberne Ehrennadel',
			'honorgoldbadge'   => 'Goldene Ehrenplakette',
			'honorsilverbadge' => 'Silberne Ehrenplakette',
			'honorletter'      => 'Ehrenbrief',
			'honorplate'       => 'Ehrenteller',
			'honormedal'       => 'Bundesmedaille'
		);

		$strAusgabe = '';

		foreach ($arrEhrungen as $strFeld => $strLabel)
		{
			if ($objPlayer->$strFeld)
			{
				$strAusgabe .= '<i>- '.$strLabel.' '.$objPlayer->$strFeld.'</i><br>';
			}
		}

		return $strAusgabe;
	}

	/**
	 * Schreibt eine Meldung ins Protokoll, sofern eines vorhanden ist.
	 *
	 * @param string $strLevel   'info' oder 'error'
	 * @param string $strMessage Der Text der Meldung
	 *
	 * @return void
	 */
	private function log(string $strLevel, string $strMessage): void
	{
		if (null === $this->logger)
		{
			return;
		}

		$this->logger->$strLevel('[Spielerregister] '.$strMessage);
	}
}
