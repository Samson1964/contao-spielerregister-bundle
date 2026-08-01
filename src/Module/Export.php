<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Module;

use Contao\Backend;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\Response;

/**
 * CSV-Export des Spielerregisters.
 *
 * Wird über die Schaltfläche "CSV-Export" in der Übersicht des Backend-Moduls
 * aufgerufen (key=export).
 */
class Export extends Backend
{
	/**
	 * @var string Trennzeichen der CSV-Datei
	 */
	protected const TRENNZEICHEN = ';';

	/**
	 * Erzeugt die CSV-Datei und schickt sie als Download an den Browser.
	 *
	 * Der Download wird als ResponseException ausgelöst, weil Contao den
	 * Rückgabewert dieser Methode sonst in die Backend-Seite einbauen würde –
	 * die CSV-Daten stünden dann im Seitenrumpf statt in einer Datei.
	 *
	 * @param DataContainer|null $dc Der aufrufende Data Container; wird nicht
	 *                               ausgewertet, weil immer die komplette
	 *                               Tabelle exportiert wird
	 *
	 * @return void Die Methode kehrt nicht zurück, sondern wirft die Antwort
	 *
	 * @throws ResponseException Enthält die fertige CSV-Datei
	 */
	public function exportSpieler($dc = null): void
	{
		$objRow = Database::getInstance()
			->prepare("SELECT surname1, firstname1, birthday, deathday, importance FROM tl_spielerregister ORDER BY surname1, firstname1")
			->execute();

		$output = '"Name"'.self::TRENNZEICHEN.'"Vorname"'.self::TRENNZEICHEN.'"Geburtsdatum"'.self::TRENNZEICHEN.'"Sterbedatum"'.self::TRENNZEICHEN.'"Wertung"'."\n";

		while ($objRow->next())
		{
			// Anführungszeichen im Inhalt würden die Spalten zerreißen
			$arrZeile = array_map(
				static function ($varValue)
				{
					return str_replace('"', '""', (string) $varValue);
				},
				$objRow->row()
			);

			$output .= '"'.implode('"'.self::TRENNZEICHEN.'"', $arrZeile).'"'."\n";
		}

		$exportFile = 'Spielerregister-Export_'.date('Ymd-Hi').'.csv';

		$response = new Response($output);
		$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
		$response->headers->set('Content-Transfer-Encoding', 'binary');
		$response->headers->set('Content-Disposition', 'attachment; filename="'.$exportFile.'"');
		$response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Expires', '0');

		throw new ResponseException($response);
	}
}
