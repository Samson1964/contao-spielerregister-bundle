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
use Schachbulle\ContaoSpielerregisterBundle\Classes\Visitenkarte;

/**
 * Prüft die Platzhalter der Visitenkarten.
 *
 * Nicht geprüft wird Visitenkarte::erzeuge(): Foto, Token-Parser und
 * Insert-Tags brauchen eine laufende Contao-Installation.
 */
class VisitenkarteTest extends TestCase
{
	/**
	 * Liefert einen lebenden Beispielspieler.
	 *
	 * @return array<string, mixed>
	 */
	private static function person(array $arrAbweichend = array()): array
	{
		return array_merge(array
		(
			'id'               => 17,
			'firstname1'       => 'Matthias',
			'surname1'         => 'Blübaum',
			'birthday'         => 19970119,
			'birthplace'       => 'Lemgo',
			'death'            => '',
			'deathday'         => 0,
			'deathplace'       => '',
			'hideLifedata'     => '',
			'importance'       => 8,
			'fide_id'          => 24651516,
			'nuliga_id'        => 'NU1234567',
			'wikipedia'        => 'Matthias Blübaum',
			'gm_title'         => '1',
			'im_title'         => '1',
			'social_youtube'   => '',
			'social_x'         => '@blue',
			'social_facebook'  => '',
			'social_instagram' => 'https://www.instagram.com/blue/',
			'multiSRC'         => 'a:0:{}',
			'links'            => 'a:0:{}',
			'cardSRC'          => "\x01\x02",
		), $arrAbweichend);
	}

	public function testSpaltenWerdenAlsZeichenkettenAngeboten(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(), array(), '20260915');

		$this->assertSame('NU1234567', $arrTokens['nuliga_id']);
		$this->assertSame('8', $arrTokens['importance']);
		$this->assertSame('17', $arrTokens['id']);
	}

	public function testBinaerUndSerialisiertBleibenDraussen(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(), array(), '20260915');

		$this->assertArrayNotHasKey('multiSRC', $arrTokens);
		$this->assertArrayNotHasKey('links', $arrTokens);
		$this->assertArrayNotHasKey('cardSRC', $arrTokens);
	}

	public function testNamen(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(), array(), '20260915');

		$this->assertSame('Matthias Blübaum', $arrTokens['name']);
		$this->assertSame('Blübaum, Matthias', $arrTokens['name_umgekehrt']);

		$arrOhneVorname = Visitenkarte::erzeugeTokens(self::person(array('firstname1' => '')), array(), '20260915');
		$this->assertSame('Blübaum', $arrOhneVorname['name']);
		$this->assertSame('Blübaum', $arrOhneVorname['name_umgekehrt']);
	}

	public function testAlterLebend(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(), array(), '20260915');

		$this->assertSame('29', $arrTokens['alter']);
		$this->assertSame('19.01.1997', $arrTokens['geburtstag_text']);
		$this->assertSame('', $arrTokens['todestag_text']);
	}

	public function testAlterBeimTod(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(array('birthday' => 18681224, 'death' => '1', 'deathday' => 19410111)), array(), '20260915');

		$this->assertSame('72', $arrTokens['alter']);
		$this->assertSame('11.01.1941', $arrTokens['todestag_text']);
	}

	public function testAlterBleibtLeerBeiUnvollstaendigemDatum(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(array('birthday' => 19970000)), array(), '20260915');

		$this->assertSame('', $arrTokens['alter']);
	}

	public function testLebensdatenAusblenden(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(array('hideLifedata' => '1')), array(), '20260915');

		// Auch die rohen Spalten bleiben leer, sonst unterliefe eine Vorlage die Einstellung
		$this->assertSame('', $arrTokens['birthday']);
		$this->assertSame('', $arrTokens['birthplace']);
		$this->assertSame('', $arrTokens['alter']);
		$this->assertSame('', $arrTokens['lebensdaten']);
		$this->assertSame('', $arrTokens['geburtstag_text']);

		// Der Name bleibt natürlich sichtbar
		$this->assertSame('Matthias Blübaum', $arrTokens['name']);
	}

	public function testHoechsterTitel(): void
	{
		$this->assertSame('GM', Visitenkarte::hoechsterTitel(self::person()));
		$this->assertSame('WIM', Visitenkarte::hoechsterTitel(array('wim_title' => '1')));
		$this->assertSame('', Visitenkarte::hoechsterTitel(array()));
	}

	public function testAdressen(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(), array(), '20260915');

		$this->assertSame('https://ratings.fide.com/profile/24651516', $arrTokens['fide_url']);
		$this->assertSame('https://de.wikipedia.org/wiki/Matthias_Blübaum', $arrTokens['wikipedia_url']);
		$this->assertSame('https://x.com/blue', $arrTokens['x_url']);
		$this->assertSame('https://www.instagram.com/blue/', $arrTokens['instagram_url']);
		$this->assertSame('', $arrTokens['youtube_url']);

		$arrOhne = Visitenkarte::erzeugeTokens(self::person(array('fide_id' => 0, 'wikipedia' => '')), array(), '20260915');
		$this->assertSame('', $arrOhne['fide_url']);
		$this->assertSame('', $arrOhne['wikipedia_url']);
	}

	public function testZusatzwerteWerdenDurchgereicht(): void
	{
		$arrTokens = Visitenkarte::erzeugeTokens(self::person(), array('bild' => '<figure></figure>', 'detail_link' => 'person/player/17.html'), '20260915');

		$this->assertSame('<figure></figure>', $arrTokens['bild']);
		$this->assertSame('', $arrTokens['bild_url']);
		$this->assertSame('person/player/17.html', $arrTokens['detail_link']);
	}
}
