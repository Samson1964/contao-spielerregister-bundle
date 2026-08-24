<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Classes;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\File;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * CSV-Import des Spielerregisters im Backend.
 *
 * Aufgerufen über die Schaltfläche "CSV-Import" in der Übersicht (key=import).
 * Importiert werden alle Personendaten außer den Fotos (Feld multiSRC und die
 * Fototabelle tl_spielerregister_images) und den Weblinks (Feld "links"): Beide
 * stehen als verschachtelte Wizard-Daten in der Datenbank und lassen sich in
 * einer flachen CSV-Zeile nicht sinnvoll abbilden – sie bleiben der manuellen
 * Pflege im Backend vorbehalten.
 *
 * Die erste Zeile der Datei muss die Spaltennamen enthalten (siehe self::SPALTEN
 * für die erlaubten Namen); die Reihenfolge der Spalten ist beliebig, unbekannte
 * Spalten werden ignoriert. Existiert bereits eine Person mit demselben Vor- und
 * Nachnamen, wird die Zeile übersprungen statt einen Doppelgänger anzulegen.
 */
class Import extends Backend
{
	/**
	 * Importierbare Spalten und ihre Umwandlung, siehe
	 * Helper::wandleImportwert() für die Bedeutung der Typnamen. Nicht
	 * aufgeführte Spalten der CSV-Datei werden stillschweigend ignoriert; id,
	 * tstamp, createtime, alias, multiSRC und links lassen sich absichtlich
	 * nicht importieren.
	 *
	 * @var array<string, string>
	 */
	private const SPALTEN = array(
		'title'            => 'text',
		'surname1'         => 'text',
		'firstname1'       => 'text',
		'surname2'         => 'text',
		'firstname2'       => 'text',
		'surname3'         => 'text',
		'firstname3'       => 'text',
		'surname4'         => 'text',
		'firstname4'       => 'text',
		'birthday'         => 'datum',
		'birthday_alt'     => 'datum',
		'birthplace'       => 'text',
		'deathday'         => 'datum',
		'deathday_alt'     => 'datum',
		'deathplace'       => 'text',
		'death'            => 'checkbox',
		'hideLifedata'     => 'checkbox',
		'shortinfo'        => 'text',
		'longinfo'         => 'text',
		'wikipedia'        => 'text',
		'fide_id'          => 'zahl',
		'dewis_id'         => 'zahl',
		'chessgames_id'    => 'text',
		'chess365_id'      => 'text',
		'chess_id'         => 'text',
		'homepage'         => 'text',
		'importance'       => 'bedeutung',
		'gm_title'         => 'checkbox',
		'gm_date'          => 'datum',
		'im_title'         => 'checkbox',
		'im_date'          => 'datum',
		'wgm_title'        => 'checkbox',
		'wgm_date'         => 'datum',
		'wim_title'        => 'checkbox',
		'wim_date'         => 'datum',
		'honorpresident'   => 'text',
		'honormember'      => 'text',
		'honorgoldpin'     => 'text',
		'honorsilverpin'   => 'text',
		'honorgoldbadge'   => 'text',
		'honorsilverbadge' => 'text',
		'honorletter'      => 'text',
		'honorplate'       => 'text',
		'honormedal'       => 'text',
		'intern'           => 'text',
		'nohighlighting'   => 'checkbox',
		'active'           => 'checkbox',
	);

	/**
	 * Zeigt das Upload-Formular an bzw. verarbeitet die hochgeladene Datei.
	 *
	 * @param DataContainer|null $dc Der aufrufende Data Container; wird nicht
	 *                               ausgewertet, weil der Import unabhängig
	 *                               vom aktuell gewählten Datensatz läuft
	 *
	 * @return string Leerer String, wenn der Import nicht angefordert wurde,
	 *                sonst das HTML des Formulars (der Upload selbst leitet
	 *                über einen Redirect zur Liste zurück)
	 */
	public function importSpieler($dc = null): string
	{
		if (Input::get('key') !== 'import')
		{
			return '';
		}

		$objUploader = new FileUpload();

		if (Input::post('FORM_SUBMIT') === 'tl_table_import')
		{
			$this->verarbeiteUpload($objUploader);
		}

		return $this->erzeugeFormular($objUploader);
	}

	/**
	 * Liest die hochgeladenen CSV-Dateien ein und schreibt die Datensätze.
	 *
	 * @param FileUpload $objUploader
	 *
	 * @return void Die Methode leitet am Ende auf die Liste zurück und kehrt
	 *              nicht regulär zurück
	 */
	private function verarbeiteUpload(FileUpload $objUploader): void
	{
		$arrUploaded = $objUploader->uploadTo('system/tmp');

		if (empty($arrUploaded))
		{
			Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields'] ?? 'Bitte wählen Sie eine Datei aus.');
			$this->reload();
		}

		$strSeparator = self::getSeparator();

		foreach ($arrUploaded as $strCsvFile)
		{
			$objFile = new File($strCsvFile);

			if ($objFile->extension !== 'csv')
			{
				Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'] ?? 'Dateityp "%s" wird nicht unterstützt.', $objFile->extension));
				continue;
			}

			$arrTabelle = array();
			$resFile = $objFile->handle;

			while (($arrZeile = fgetcsv($resFile, 0, $strSeparator)) !== false)
			{
				$arrTabelle[] = $arrZeile;
			}

			if (\count($arrTabelle) < 2)
			{
				Message::addError($objFile->name.' enthält keine Datensätze.');
				continue;
			}

			$this->importiereTabelle($objFile->name, $arrTabelle);
		}

		// Zur Übersicht zurückkehren (key=import aus der URL entfernen)
		System::setCookie('BE_PAGE_OFFSET', 0, 0);
		$this->redirect(str_replace('&key=import', '', Environment::get('request')));
	}

	/**
	 * Schreibt die eingelesenen CSV-Zeilen als neue Personen in die Datenbank.
	 *
	 * Jede Zeile wird einzeln in einer eigenen Fehlerbehandlung verarbeitet:
	 * Eine einzelne fehlerhafte Zeile (etwa ein zu langer Wert für eine
	 * Datenbankspalte) soll nicht den Import der restlichen Datei abbrechen.
	 *
	 * @param string                  $strDateiname Name der Datei, für die Meldungen
	 * @param list<list<string|null>> $arrTabelle   Alle Zeilen der CSV-Datei
	 *                                              inklusive der Kopfzeile
	 *
	 * @return void
	 */
	private function importiereTabelle(string $strDateiname, array $arrTabelle): void
	{
		$arrKopf = array_map('trim', array_map('strval', array_shift($arrTabelle)));

		// Ein von Excel gesetztes UTF-8-BOM klebt an der ersten Spaltenüberschrift
		if (isset($arrKopf[0]))
		{
			$arrKopf[0] = ltrim($arrKopf[0], "\xEF\xBB\xBF");
		}

		// Nur bekannte, importierbare Spalten übernehmen; die Reihenfolge in
		// der Datei ist dabei beliebig
		$arrSpalten = array();

		foreach ($arrKopf as $intIndex => $strSpalte)
		{
			if (isset(self::SPALTEN[$strSpalte]))
			{
				$arrSpalten[$intIndex] = $strSpalte;
			}
		}

		if (!\in_array('surname1', $arrSpalten, true))
		{
			Message::addError($strDateiname.' enthält keine Spalte "surname1" (Nachname) und wurde nicht importiert.');

			return;
		}

		$objDatabase = Database::getInstance();

		$intImportiert = 0;
		$intOhneNamen = 0;
		$arrUebersprungen = array();
		$arrFehlgeschlagen = array();

		foreach ($arrTabelle as $arrZeile)
		{
			$arrWerte = array();

			foreach ($arrSpalten as $intIndex => $strSpalte)
			{
				$arrWerte[$strSpalte] = Helper::wandleImportwert(self::SPALTEN[$strSpalte], (string) ($arrZeile[$intIndex] ?? ''));
			}

			$strNachname = trim((string) ($arrWerte['surname1'] ?? ''));
			$strVorname = trim((string) ($arrWerte['firstname1'] ?? ''));
			$strAnzeige = trim($strNachname.', '.$strVorname, ', ');

			if ('' === $strNachname)
			{
				$intOhneNamen++;
				continue;
			}

			// Existiert bereits eine Person mit demselben Namen, wird die Zeile
			// übersprungen statt einen Doppelgänger anzulegen. Die Prüfung läuft
			// gegen die Datenbank und nicht nur gegen die schon eingelesenen
			// Zeilen, damit auch Dubletten innerhalb derselben Datei auffliegen –
			// die zuerst eingefügte Zeile steht zu diesem Zeitpunkt schon drin.
			$objTreffer = $objDatabase
				->prepare('SELECT id FROM tl_spielerregister WHERE LOWER(surname1) = LOWER(?) AND LOWER(firstname1) = LOWER(?)')
				->execute($strNachname, $strVorname);

			if ($objTreffer->numRows)
			{
				$arrUebersprungen[] = $strAnzeige;
				continue;
			}

			$arrWerte['alias'] = Helper::erzeugeAlias($arrWerte);
			$arrWerte['tstamp'] = time();
			$arrWerte['createtime'] = time(); // Damit greift auch der Cronjob "Neue Einträge"

			$strFelder = implode(', ', array_keys($arrWerte));
			$strPlatzhalter = implode(', ', array_fill(0, \count($arrWerte), '?'));

			try
			{
				$objDatabase
					->prepare('INSERT INTO tl_spielerregister ('.$strFelder.') VALUES ('.$strPlatzhalter.')')
					->execute(...array_values($arrWerte));

				$intImportiert++;
			}
			catch (\Throwable $e)
			{
				$arrFehlgeschlagen[] = $strAnzeige.' ('.$e->getMessage().')';
			}
		}

		Message::addConfirmation($strDateiname.': '.$intImportiert.' Personen importiert.');

		if ($intOhneNamen > 0)
		{
			Message::addInfo($strDateiname.': '.$intOhneNamen.' Zeile(n) ohne Nachname übersprungen.');
		}

		if ($arrUebersprungen)
		{
			Message::addInfo($strDateiname.': Bereits vorhanden, deshalb nicht importiert: '.implode('; ', $arrUebersprungen));
		}

		if ($arrFehlgeschlagen)
		{
			Message::addError($strDateiname.': Nicht importiert wegen eines Fehlers: '.implode('; ', $arrFehlgeschlagen));
		}
	}

	/**
	 * Ermittelt das im Formular gewählte CSV-Trennzeichen.
	 *
	 * @return string Ein einzelnes Zeichen (Komma, Semikolon oder Tabulator)
	 */
	private static function getSeparator(): string
	{
		switch (Input::post('separator'))
		{
			case 'semicolon':
				return ';';

			case 'tabulator':
				return "\t";

			default:
				return ',';
		}
	}

	/**
	 * Erzeugt das Upload-Formular für den Import.
	 *
	 * Die Feldstruktur (fieldset.tl_tbox + div.widget) entspricht wörtlich dem
	 * Kern-Template be_csv_import.html5 (identisch in Contao 4.13 und 5): Ohne
	 * diese Klassen greift das Backend-Stylesheet nicht, Trennzeichen und
	 * Quelldatei hängen dann unformatiert am linken Rand.
	 *
	 * @param FileUpload $objUploader
	 *
	 * @return string Das fertige HTML der Backend-Seite
	 */
	private function erzeugeFormular(FileUpload $objUploader): string
	{
		// Request-Token über den Service holen
		// (die Konstante REQUEST_TOKEN existiert in Contao 5 nicht mehr)
		$strToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

		$objTemplate = new BackendTemplate('be_spielerregister_import');
		$objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['tw_import'][1] ?? 'CSV-Import';
		$objTemplate->backUrl = str_replace('&key=import', '', Environment::get('request'));
		$objTemplate->backBT = $GLOBALS['TL_LANG']['MSC']['backBT'] ?? 'Zurück';
		$objTemplate->backBTTitle = $GLOBALS['TL_LANG']['MSC']['backBTTitle'] ?? '';
		$objTemplate->action = Environment::get('request');
		$objTemplate->requestToken = $strToken;
		$objTemplate->fileMaxSize = Config::get('maxFileSize');
		$objTemplate->separatorLabel = $GLOBALS['TL_LANG']['MSC']['separator'][0] ?? 'Trennzeichen';
		$objTemplate->separators = array
		(
			array('value' => 'comma', 'label' => $GLOBALS['TL_LANG']['MSC']['comma'] ?? 'Komma'),
			array('value' => 'semicolon', 'label' => $GLOBALS['TL_LANG']['MSC']['semicolon'] ?? 'Semikolon'),
			array('value' => 'tabulator', 'label' => $GLOBALS['TL_LANG']['MSC']['tabulator'] ?? 'Tabulator'),
		);
		$objTemplate->sourceLabel = $GLOBALS['TL_LANG']['MSC']['source'][0] ?? 'Quelldatei';
		$objTemplate->uploader = $objUploader->generateMarkup();
		$objTemplate->spaltenHelp = 'Die erste Zeile muss die Spaltennamen enthalten, die Reihenfolge ist beliebig. Erkannt werden: '.implode(', ', array_keys(self::SPALTEN)).'. Fotos und Weblinks lassen sich nicht importieren, dafür bitte die Person nach dem Import im Backend öffnen. Existiert bereits eine Person mit demselben Vor- und Nachnamen, wird die Zeile übersprungen.';
		$objTemplate->submitLabel = $GLOBALS['TL_LANG']['MSC']['tw_import'][0] ?? 'Importieren';

		return $objTemplate->parse();
	}
}
