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
 * Nicht geprüft werden getRegister(), getPlayerlink(), getLinks(), seitenUrl()
 * und erzeugeAlias(): Sie greifen auf Datenbank, Seitenstruktur bzw. andere
 * Contao-Klassen zu und brauchen dafür eine laufende Contao-Installation.
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

	public function testWandleImportwertText(): void
	{
		$this->assertSame('Lasker', Helper::wandleImportwert('text', '  Lasker  '));
		$this->assertSame('', Helper::wandleImportwert('text', ''));
	}

	public function testWandleImportwertDatum(): void
	{
		// Dieselbe Regel wie im Backend-Formular, siehe Helper::putDate()
		$this->assertSame('18681224', Helper::wandleImportwert('datum', '24.12.1868'));
		$this->assertSame(0, Helper::wandleImportwert('datum', 'irgendwann'));
	}

	public function testWandleImportwertCheckbox(): void
	{
		// Jeder nicht-leere, nicht-"0"-Wert gilt als angehakt – so lassen sich
		// in der Tabellenkalkulation gängige Markierungen wie "x" oder "ja"
		// benutzen, ohne dass exakt "1" eingetragen werden muss
		$this->assertSame('1', Helper::wandleImportwert('checkbox', '1'));
		$this->assertSame('1', Helper::wandleImportwert('checkbox', 'x'));
		$this->assertSame('1', Helper::wandleImportwert('checkbox', 'ja'));
		$this->assertSame('', Helper::wandleImportwert('checkbox', ''));
		$this->assertSame('', Helper::wandleImportwert('checkbox', '0'));
		$this->assertSame('', Helper::wandleImportwert('checkbox', '  '));
	}

	public function testWandleImportwertZahl(): void
	{
		$this->assertSame(1928, Helper::wandleImportwert('zahl', '1928'));
		$this->assertSame(0, Helper::wandleImportwert('zahl', ''));
		$this->assertSame(0, Helper::wandleImportwert('zahl', 'keine Zahl'));
	}

	public function testBereinigeBenutzername(): void
	{
		$this->assertSame('magnus', Helper::bereinigeBenutzername('https://www.instagram.com/magnus/'));
		$this->assertSame('foo', Helper::bereinigeBenutzername('x.com/foo?lang=de'));
		$this->assertSame('MagnusCarlsen', Helper::bereinigeBenutzername('@MagnusCarlsen'));
		$this->assertSame('alt', Helper::bereinigeBenutzername('https://youtube.com/user/alt'));
		$this->assertSame('handle', Helper::bereinigeBenutzername('https://www.youtube.com/@handle'));
		$this->assertSame('schach.bund_de-1', Helper::bereinigeBenutzername('  schach.bund_de-1  '));
		$this->assertSame('', Helper::bereinigeBenutzername(''));
		$this->assertSame('', Helper::bereinigeBenutzername(null));

		// Alles außer Buchstaben, Ziffern, Punkt, Unterstrich und Bindestrich fällt weg
		$this->assertSame('evilscript', Helper::bereinigeBenutzername('evil"<script>'));
	}

	public function testSozialerLink(): void
	{
		$this->assertSame('https://www.youtube.com/@handle', Helper::sozialerLink('youtube', 'handle'));
		$this->assertSame('https://x.com/foo', Helper::sozialerLink('x', '@foo'));
		$this->assertSame('https://www.facebook.com/schachbund', Helper::sozialerLink('facebook', 'https://www.facebook.com/schachbund'));
		$this->assertSame('https://www.instagram.com/magnus/', Helper::sozialerLink('instagram', 'magnus'));
		$this->assertSame('', Helper::sozialerLink('x', ''));
		$this->assertSame('', Helper::sozialerLink('myspace', 'foo'));
	}

	public function testWandleImportwertBenutzername(): void
	{
		$this->assertSame('foo', Helper::wandleImportwert('benutzername', 'https://x.com/foo'));
	}

	public function testWandleImportwertBedeutung(): void
	{
		$this->assertSame(7, Helper::wandleImportwert('bedeutung', '7'));

		// Vorgabewert wie im Auswahlfeld des Backends
		$this->assertSame(5, Helper::wandleImportwert('bedeutung', ''));

		// Werte außerhalb von 1 bis 10 werden auf den gültigen Bereich begrenzt,
		// damit eine falsche CSV-Eingabe nicht in der Datenbank landet
		$this->assertSame(10, Helper::wandleImportwert('bedeutung', '99'));
		$this->assertSame(1, Helper::wandleImportwert('bedeutung', '-3'));
	}
}
