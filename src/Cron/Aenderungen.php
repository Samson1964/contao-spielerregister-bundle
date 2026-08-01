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
use Psr\Log\LoggerInterface;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Cronjob "Neue Einträge".
 *
 * Meldet einmal täglich die in den letzten 24 Stunden neu aufgenommenen
 * Personen an den eingestellten Newsletter-Verteiler.
 *
 * Der Cronjob ersetzt das frühere Aufrufskript public/aenderungen.php, das
 * system/initialize.php eingebunden hat – diesen Weg gibt es in Contao 5 nicht
 * mehr. Der Verteiler und die Absenderdaten stammen jetzt aus den
 * Einstellungen und stehen nicht mehr fest im Quelltext.
 *
 * ACHTUNG: Dieser Cronjob verschickt E-Mails an echte Empfänger. Er tut das
 * erst, wenn die Einstellung "Newsletter über neue Einträge automatisch
 * versenden" (spielerregister_cron_neu) gesetzt ist.
 */
class Aenderungen
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
	 * Gibt es keine neuen Datensätze, passiert nichts – es wird also keine
	 * leere E-Mail verschickt. Die Empfänger stehen im Blindkopie-Feld, damit
	 * die Adressen untereinander nicht sichtbar werden.
	 *
	 * @return void
	 */
	public function __invoke(): void
	{
		// Contao-Framework initialisieren (Legacy-Klassen Config, Database, Email)
		$this->framework->initialize();

		if (!Config::get('spielerregister_cron_neu'))
		{
			return; // Automatikversand ist nicht eingeschaltet
		}

		$intChannel = (int) Config::get('spielerregister_newsletter');
		$strAbsender = trim((string) Config::get('spielerregister_mailAbsender'));

		if (!$intChannel || '' === $strAbsender)
		{
			$this->log('error', 'Newsletter über neue Einträge übersprungen: Es fehlt der Newsletter-Verteiler oder die Absenderadresse.');

			return;
		}

		$strAusgabe = $this->erzeugeInhalt();

		if ('' === $strAusgabe)
		{
			return; // Keine neuen Einträge
		}

		$arrEmpfaenger = $this->getEmpfaenger($intChannel);

		if (!$arrEmpfaenger)
		{
			return; // Niemand im Verteiler
		}

		$strName = trim((string) Config::get('spielerregister_mailAbsendername'));
		$strFuss = (string) Config::get('spielerregister_mailFuss');

		$content = '<h1>Neue Einträge im Spielerregister</h1>';
		$content .= '<p>Folgende Personen wurden <b>neu</b> in unsere Datenbank aufgenommen:</p>'.$strAusgabe;
		$content .= $strFuss;

		$objEmail = new Email();
		$objEmail->from = $strAbsender;
		$objEmail->charset = 'utf-8';
		$objEmail->subject = 'Spielerregister - Neue Einträge';
		$objEmail->html = $content;

		if ('' !== $strName)
		{
			$objEmail->fromName = $strName;
		}

		// An den Absender adressiert, alle Empfänger stehen in Blindkopie
		$objEmail->sendBcc($arrEmpfaenger);
		$objEmail->sendTo($strAbsender);

		$this->log('info', sprintf('Newsletter über neue Einträge an %d Empfänger versendet', \count($arrEmpfaenger)));
	}

	/**
	 * Baut die Liste der neu aufgenommenen Personen zusammen.
	 *
	 * Als neu gilt ein Datensatz, dessen Erstellungszeitpunkt (createtime)
	 * weniger als 24 Stunden zurückliegt. Ältere Datensätze ohne createtime –
	 * aus der Zeit vor Einführung des Feldes – tauchen damit nie auf.
	 *
	 * @return string Die Personen als HTML-Liste, bei keinem neuen Datensatz
	 *                ein leerer String
	 */
	private function erzeugeInhalt(): string
	{
		// Zeit festlegen, ab der ein Datensatz als neu gilt
		$neuzeit = time() - (24 * 3600);

		$objPlayers = Database::getInstance()
			->prepare("SELECT * FROM tl_spielerregister WHERE active = 1 AND createtime >= ?")
			->execute($neuzeit);

		if (!$objPlayers->numRows)
		{
			return '';
		}

		$strBasis = rtrim((string) Config::get('spielerregister_mailUrl'), '/');
		$ausgabe = '<ul>';

		while ($objPlayers->next())
		{
			$strName = trim($objPlayers->firstname1.' '.$objPlayers->surname1);
			$strLink = Helper::getPlayerlink($objPlayers->id);

			$ausgabe .= '<li>';
			$ausgabe .= '<b>'.('' !== $strLink ? '<a href="'.$strBasis.$strLink.'">'.$strName.'</a>' : $strName).'</b><br>';
			$ausgabe .= '* '.Helper::getDate($objPlayers->birthday).' in '.$objPlayers->birthplace.'<br>';

			if ($objPlayers->death)
			{
				$ausgabe .= '&dagger; '.Helper::getDate($objPlayers->deathday).' in '.$objPlayers->deathplace.'<br>';
			}

			$ausgabe .= 'Zeitgeschichtliche Bedeutung (1 gering - 10 hoch): <b>'.$objPlayers->importance.'</b><br>';
			$ausgabe .= '<i>'.$objPlayers->shortinfo.'</i>';
			$ausgabe .= '</li>';
		}

		return $ausgabe.'</ul>';
	}

	/**
	 * Liest die aktiven Empfänger des Newsletter-Verteilers.
	 *
	 * @param int $intChannel ID des Newsletter-Verteilers
	 *
	 * @return array<int, string> Die E-Mail-Adressen der Empfänger
	 */
	private function getEmpfaenger(int $intChannel): array
	{
		$objNewsletter = Database::getInstance()
			->prepare("SELECT email FROM tl_newsletter_recipients WHERE active = 1 AND pid = ?")
			->execute($intChannel);

		$arrEmpfaenger = array();

		while ($objNewsletter->next())
		{
			$arrEmpfaenger[] = $objNewsletter->email;
		}

		return $arrEmpfaenger;
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
