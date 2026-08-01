<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Tests\Classes;

use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Datum;

/**
 * Prüft das Rechnen mit den unvollständigen Datumsangaben des Registers.
 */
class DatumTest extends TestCase
{
	public function testZerlegeVollstaendigesDatum(): void
	{
		$this->assertSame(array('tag' => 24, 'monat' => 12, 'jahr' => 1868), Datum::zerlege(18681224));
		$this->assertSame(array('tag' => 24, 'monat' => 12, 'jahr' => 1868), Datum::zerlege('24.12.1868'));
	}

	public function testZerlegeUnvollstaendigesDatum(): void
	{
		// Nur Jahr und Monat bekannt
		$this->assertSame(array('tag' => 0, 'monat' => 1, 'jahr' => 1941), Datum::zerlege(19410100));
		$this->assertSame(array('tag' => 0, 'monat' => 1, 'jahr' => 1941), Datum::zerlege('194101'));

		// Nur das Jahr bekannt
		$this->assertSame(array('tag' => 0, 'monat' => 0, 'jahr' => 1900), Datum::zerlege(19000000));
		$this->assertSame(array('tag' => 0, 'monat' => 0, 'jahr' => 1900), Datum::zerlege('1900'));
	}

	public function testZerlegeOhneAuswertbarenWert(): void
	{
		$this->assertNull(Datum::zerlege(0));
		$this->assertNull(Datum::zerlege(''));
		$this->assertNull(Datum::zerlege('irgendwann'));
		$this->assertNull(Datum::zerlege('00000000')); // Jahr fehlt
	}

	public function testFormatiere(): void
	{
		$this->assertSame('24.12.1868', Datum::formatiere(18681224));
		$this->assertSame('01.1941', Datum::formatiere(19410100));
		$this->assertSame('1900', Datum::formatiere(19000000));
	}

	public function testFormatiereLeerwert(): void
	{
		$this->assertSame('', Datum::formatiere(0));
		$this->assertSame('unbekannt', Datum::formatiere(0, 'unbekannt'));
		$this->assertSame('unbekannt', Datum::formatiere('', 'unbekannt'));
	}

	public function testMonatstag(): void
	{
		$this->assertSame('24. Dezember', Datum::monatstag(18681224));
		$this->assertSame('1. Januar', Datum::monatstag(19410101));

		// Ohne Monat bleibt der Name leer
		$this->assertSame('0. ', Datum::monatstag(19000000));
	}

	public function testJahr(): void
	{
		$this->assertSame('1868', Datum::jahr(18681224));
	}

	public function testJahrestagAlterImSelbenJahr(): void
	{
		// Zeitraum vom 1. bis 31. Dezember 2026, Geburtstag 24.12.1868
		$this->assertSame(158, Datum::jahrestagAlter(18681224, 20261201, 20261231));
	}

	public function testJahrestagAlterUeberDenJahreswechsel(): void
	{
		// Zeitraum 15.12.2026 bis 15.01.2027
		// Der 24. Dezember fällt noch ins alte Jahr
		$this->assertSame(158, Datum::jahrestagAlter(18681224, 20261215, 20270115));

		// Der 11. Januar fällt schon ins neue Jahr
		$this->assertSame(86, Datum::jahrestagAlter(19410111, 20261215, 20270115));
	}

	public function testJahrestagAlterAusserhalbDesZeitraums(): void
	{
		// Der 5. Juni liegt weder im Dezember noch im Januar
		$this->assertFalse(Datum::jahrestagAlter(19410605, 20261215, 20270115));
	}

	public function testAlterZwischen(): void
	{
		// Lasker: * 24.12.1868, † 11.01.1941 – der Geburtstag war schon
		$this->assertSame(72, Datum::alterZwischen(18681224, 19410111));

		// Geburtstag im Todesjahr noch nicht erreicht
		$this->assertSame(71, Datum::alterZwischen(18681224, 19401201));

		// Genau am Geburtstag gestorben
		$this->assertSame(72, Datum::alterZwischen(18681224, 19401224));
	}

	public function testAlterZwischenUnvollstaendig(): void
	{
		// Ohne Tag und Monat lässt sich kein Alter berechnen
		$this->assertFalse(Datum::alterZwischen(18680000, 19410111));
		$this->assertFalse(Datum::alterZwischen(18681224, 19410000));
		$this->assertFalse(Datum::alterZwischen(0, 19410111));

		// 31. Februar gibt es nicht
		$this->assertFalse(Datum::alterZwischen(18680231, 19410111));
	}

	public function testIsoDatum(): void
	{
		$this->assertSame('1868-12-24', Datum::isoDatum(18681224));

		// Fehlender Monat wird zum Juni, fehlender Tag zum Monatsersten
		$this->assertSame('1868-06-01', Datum::isoDatum(18680000));
		$this->assertSame('1868-03-01', Datum::isoDatum(18680300));
		$this->assertNull(Datum::isoDatum(0));
	}

	public function testZeitspanne(): void
	{
		$interval = Datum::zeitspanne(18681224, 19500101);

		$this->assertNotNull($interval);
		$this->assertSame(81, $interval->y);
		$this->assertSame(0, $interval->m);
		$this->assertSame(8, $interval->d);

		$this->assertNull(Datum::zeitspanne(18681224, 0));
	}
}
