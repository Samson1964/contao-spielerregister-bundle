<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Tests\Klassen;

use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Prüft die Hilfsfunktionen, die ohne Contao auskommen.
 *
 * Nicht geprüft werden getRegister(), getPlayerlink() und getLinks(): Sie
 * greifen auf Datenbank, Seitenstruktur bzw. Contao-Klassen zu und brauchen
 * dafür eine laufende Contao-Installation.
 */
class HelperTest extends TestCase
{
	public function testGetDateWandeltDatenbankwerteUm(): void
	{
		$this->assertSame('24.12.1868', Helper::getDate(18681224));
		$this->assertSame('01.1941', Helper::getDate(194101));
		$this->assertSame('1900', Helper::getDate(1900));
	}

	public function testGetDateOhneWert(): void
	{
		$this->assertSame('', Helper::getDate(0));
		$this->assertSame('', Helper::getDate('0'));
	}

	public function testGetDateLaesstEingegebeneDatenUnveraendert(): void
	{
		// Beim erneuten Laden nach einer Eingabe darf der Wert nicht
		// ein zweites Mal umgewandelt werden
		$this->assertSame('24.12.1868', Helper::getDate('24.12.1868'));
		$this->assertSame('unbekannt', Helper::getDate('unbekannt'));
		$this->assertNull(Helper::getDate(null));
	}

	public function testPutDate(): void
	{
		$this->assertSame('18681224', Helper::putDate('24.12.1868'));
		$this->assertSame('19410100', Helper::putDate('01.1941'));
		$this->assertSame('19000000', Helper::putDate('1900'));
	}

	public function testPutDateOhneAuswertbaresFormat(): void
	{
		$this->assertSame(0, Helper::putDate(''));
		$this->assertSame(0, Helper::putDate('irgendwann'));
	}

	public function testIsDigit(): void
	{
		$this->assertTrue(Helper::is_digit(123));
		$this->assertTrue(Helper::is_digit('123'));

		$this->assertFalse(Helper::is_digit('12.3'));
		$this->assertFalse(Helper::is_digit(1.5));
		$this->assertFalse(Helper::is_digit(true));
		$this->assertFalse(Helper::is_digit(null));
		$this->assertFalse(Helper::is_digit(''));
	}

	public function testGetLebensdatenVollstaendig(): void
	{
		$this->assertSame(
			'* 24.12.1868 Berlinchen; &dagger; 11.01.1941 New York',
			Helper::getLebensdaten(18681224, 'Berlinchen', '1', 19410111, 'New York')
		);
	}

	public function testGetLebensdatenOhneTodesangaben(): void
	{
		// Ohne Kennzeichen "verstorben" entfällt der zweite Teil vollständig
		$this->assertSame('* 24.12.1868 Berlinchen', Helper::getLebensdaten(18681224, 'Berlinchen', '', 19410111, 'New York'));

		// Verstorben, aber ohne bekanntes Datum
		$this->assertSame('* 24.12.1868; &dagger;', Helper::getLebensdaten(18681224, '', '1', 0, ''));
	}

	public function testGetLebensdatenOhneAngaben(): void
	{
		$this->assertSame('', Helper::getLebensdaten(0, '', '', 0, ''));
	}
}
