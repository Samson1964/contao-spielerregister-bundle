<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Classes;

use Contao\CoreBundle\File\Metadata;
use Contao\Database;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Spielerregister;

/**
 * Setzt eine Visitenkarte aus einer Vorlage und einem Registereintrag zusammen.
 *
 * Eine Visitenkarte (Tabelle tl_spielerregister_cards) ist HTML mit
 * Platzhaltern. Ausgewertet wird sie mit dem Simple-Token-Parser des
 * Contao-Kerns, der in Contao 4.13 und 5 dieselbe Schreibweise versteht:
 *
 *     ##name##                     Platzhalter
 *     {if nuliga_id}…{endif}       Abfrage auf "nicht leer"
 *     {if importance >= 8}…{else}…{endif}
 *
 * Erst danach werden Insert-Tags ersetzt. Damit lassen sich Platzhalter in
 * fremde Insert-Tags einsetzen, etwa {{dwz::##nuliga_id##}} aus dem
 * Wertungsportal-Bundle.
 */
class Visitenkarte
{
	/**
	 * Spalten von tl_spielerregister, die nicht als Platzhalter angeboten
	 * werden: Datei-UUIDs sind Binärwerte, "links" ist ein serialisiertes
	 * Array — als Text in einer Vorlage wäre beides unbrauchbar.
	 *
	 * @var list<string>
	 */
	public const AUSGENOMMEN = array('multiSRC', 'links', 'cardSRC');

	/**
	 * Spalten mit Lebensdaten. Ist bei einer Person "Lebensdaten ausblenden"
	 * gesetzt, bleiben diese Platzhalter leer, damit eine Vorlage die Einstellung
	 * nicht versehentlich unterläuft.
	 *
	 * @var list<string>
	 */
	public const LEBENSDATEN = array('birthday', 'birthday_alt', 'birthplace', 'deathday', 'deathday_alt', 'deathplace');

	/**
	 * FIDE-Titel in absteigender Rangfolge, jeweils Spalte => Kürzel.
	 *
	 * @var array<string, string>
	 */
	public const TITEL = array('gm_title' => 'GM', 'im_title' => 'IM', 'wgm_title' => 'WGM', 'wim_title' => 'WIM');

	/**
	 * Stellt die Platzhalter für eine Person zusammen.
	 *
	 * Die Methode kommt ohne Contao aus und ist deshalb unit-getestet. Alles,
	 * wofür Datenbank oder Bildverarbeitung nötig sind (Foto, Link zur
	 * Detailseite), wird über $arrZusatz fertig hereingereicht.
	 *
	 * Angeboten werden alle Spalten des Datensatzes unter ihrem Spaltennamen
	 * (außer self::AUSGENOMMEN) und zusätzlich abgeleitete Werte: name,
	 * name_umgekehrt, geburtstag_text, todestag_text, lebensdaten, alter,
	 * fide_titel, fide_url, wikipedia_url, youtube_url, x_url, facebook_url,
	 * instagram_url sowie bild, bild_url und detail_link aus $arrZusatz.
	 *
	 * Alle Werte sind Zeichenketten. Der Token-Parser reicht sie unverändert an
	 * Vergleiche weiter; PHP vergleicht numerische Zeichenketten dabei als Zahl,
	 * {if importance >= 8} funktioniert also wie erwartet.
	 *
	 * @param array<string, mixed>  $arrPerson Datensatz aus tl_spielerregister
	 * @param array<string, string> $arrZusatz Fertig erzeugte Werte für bild,
	 *                                         bild_url und detail_link
	 * @param string                $strHeute  Stichtag für das Alter als JJJJMMTT;
	 *                                         leer bedeutet heute (für Tests)
	 *
	 * @return array<string, string> Platzhaltername => Wert
	 */
	public static function erzeugeTokens(array $arrPerson, array $arrZusatz = array(), string $strHeute = ''): array
	{
		$arrTokens = array();

		foreach ($arrPerson as $strFeld => $varWert)
		{
			// Nur gültige Bezeichner: Der Token-Parser von Contao 4.13 fällt
			// sonst für die ganze Vorlage auf die alte Auswertung zurück
			if (\in_array($strFeld, self::AUSGENOMMEN, true) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', (string) $strFeld))
			{
				continue;
			}

			$arrTokens[$strFeld] = \is_scalar($varWert) ? (string) $varWert : '';
		}

		$blnVerborgen = !empty($arrPerson['hideLifedata']);

		if ($blnVerborgen)
		{
			foreach (self::LEBENSDATEN as $strFeld)
			{
				if (isset($arrTokens[$strFeld]))
				{
					$arrTokens[$strFeld] = '';
				}
			}
		}

		$strVorname = trim((string) ($arrPerson['firstname1'] ?? ''));
		$strNachname = trim((string) ($arrPerson['surname1'] ?? ''));

		$arrTokens['name'] = trim($strVorname.' '.$strNachname);
		$arrTokens['name_umgekehrt'] = '' !== $strVorname ? $strNachname.', '.$strVorname : $strNachname;

		if ($blnVerborgen)
		{
			$arrTokens['geburtstag_text'] = '';
			$arrTokens['todestag_text'] = '';
			$arrTokens['lebensdaten'] = '';
			$arrTokens['alter'] = '';
		}
		else
		{
			$blnTot = !empty($arrPerson['death']);

			$arrTokens['geburtstag_text'] = Datum::formatiere($arrPerson['birthday'] ?? '');
			$arrTokens['todestag_text'] = $blnTot ? Datum::formatiere($arrPerson['deathday'] ?? '') : '';
			$arrTokens['lebensdaten'] = Helper::getLebensdaten($arrPerson['birthday'] ?? '', $arrPerson['birthplace'] ?? '', $arrPerson['death'] ?? '', $arrPerson['deathday'] ?? '', $arrPerson['deathplace'] ?? '');

			// Bei Verstorbenen das Alter beim Tod, sonst das heutige. Fehlt ein
			// Tag oder Monat, liefert alterZwischen() false und der Platzhalter
			// bleibt leer statt eine falsche Zahl zu zeigen
			$strEnde = $blnTot ? (string) ($arrPerson['deathday'] ?? '') : ('' !== $strHeute ? $strHeute : date('Ymd'));
			$intAlter = Datum::alterZwischen($arrPerson['birthday'] ?? '', $strEnde);

			$arrTokens['alter'] = false === $intAlter ? '' : (string) $intAlter;
		}

		$arrTokens['fide_titel'] = self::hoechsterTitel($arrPerson);

		$intFide = (int) ($arrPerson['fide_id'] ?? 0);
		$arrTokens['fide_url'] = $intFide > 0 ? 'https://ratings.fide.com/profile/'.$intFide : '';

		// Wie in den übrigen Templates des Bundles unverschlüsselt angehängt;
		// manche Einträge stehen schon URL-kodiert im Register
		$strWikipedia = trim((string) ($arrPerson['wikipedia'] ?? ''));
		$arrTokens['wikipedia_url'] = '' !== $strWikipedia ? 'https://de.wikipedia.org/wiki/'.str_replace(' ', '_', $strWikipedia) : '';

		foreach (array_keys(Helper::SOZIALE_NETZE) as $strNetz)
		{
			$arrTokens[$strNetz.'_url'] = Helper::sozialerLink($strNetz, $arrPerson['social_'.$strNetz] ?? '');
		}

		foreach (array('bild', 'bild_url', 'detail_link') as $strSchluessel)
		{
			$arrTokens[$strSchluessel] = (string) ($arrZusatz[$strSchluessel] ?? '');
		}

		return $arrTokens;
	}

	/**
	 * Ermittelt den höchsten FIDE-Titel einer Person.
	 *
	 * @param array<string, mixed> $arrPerson Datensatz aus tl_spielerregister
	 *
	 * @return string Kürzel wie "GM", oder ein leerer String ohne Titel
	 */
	public static function hoechsterTitel(array $arrPerson): string
	{
		foreach (self::TITEL as $strFeld => $strKuerzel)
		{
			if (!empty($arrPerson[$strFeld]))
			{
				return $strKuerzel;
			}
		}

		return '';
	}

	/**
	 * Erzeugt das fertige HTML einer Visitenkarte.
	 *
	 * Ablauf: Foto erzeugen, Insert-Tags in Kurz- und Langinfo auflösen,
	 * Platzhalter und Abfragen auswerten, zum Schluss die Insert-Tags der
	 * Vorlage ersetzen. Die Kurz- und Langinfo werden vorab aufgelöst, weil der
	 * Token-Parser Insert-Tags in eingesetzten Werten absichtlich unschädlich
	 * macht — ein {{file::…}} aus dem Register bliebe sonst als Text stehen.
	 *
	 * Eine fehlerhafte Vorlage (etwa ein {if} ohne {endif}) bricht die Seite
	 * nicht ab: Die Karte bleibt leer und der Fehler landet im Systemprotokoll.
	 *
	 * @param array<string, mixed> $arrKarte  Datensatz aus tl_spielerregister_cards
	 * @param array<string, mixed> $arrPerson Datensatz aus tl_spielerregister
	 *
	 * @return array{inhalt: string, hintergrund: string, klasse: string}
	 *               Das HTML, der Pfad des Hintergrundbildes (leer, wenn keins
	 *               gewählt ist) und die zusätzliche CSS-Klasse
	 */
	public static function erzeuge(array $arrKarte, array $arrPerson): array
	{
		$container = System::getContainer();
		$objParser = $container->get('contao.insert_tag.parser');

		$arrZusatz = array
		(
			'bild'        => '',
			'bild_url'    => '',
			'detail_link' => Helper::getPlayerlink($arrPerson['id'] ?? 0),
		);

		$strPfad = self::bildpfad($arrPerson);

		if ('' !== $strPfad)
		{
			$strName = trim((string) ($arrPerson['firstname1'] ?? '').' '.(string) ($arrPerson['surname1'] ?? ''));

			$figure = $container->get('contao.image.studio')
				->createFigureBuilder()
				->fromPath($strPfad)
				->setSize(StringUtil::deserialize($arrKarte['size'] ?? ''))
				->setMetadata(new Metadata(array(Metadata::VALUE_ALT => $strName)))
				->enableLightbox(!empty($arrKarte['fullsize']))
				->buildIfResourceExists();

			if (null !== $figure)
			{
				// Das Kern-Template "image" liefert dieselbe <figure> wie ein
				// Bild-Inhaltselement, samt Klasse float_above/_left/…
				$objBild = new FrontendTemplate('image');
				$figure->applyLegacyTemplateData($objBild, null, (string) ($arrKarte['floating'] ?? '') ?: 'above');

				$arrZusatz['bild'] = $objBild->parse();
				$arrZusatz['bild_url'] = $figure->getImage()->getImageSrc();
			}
		}

		foreach (array('shortinfo', 'longinfo') as $strFeld)
		{
			if (isset($arrPerson[$strFeld]))
			{
				$arrPerson[$strFeld] = $objParser->replaceInline((string) $arrPerson[$strFeld]);
			}
		}

		$strInhalt = '';

		try
		{
			$strInhalt = $container->get('contao.string.simple_token_parser')->parse((string) ($arrKarte['html'] ?? ''), self::erzeugeTokens($arrPerson, $arrZusatz));
			$strInhalt = $objParser->replace($strInhalt);
		}
		catch (\Throwable $e)
		{
			if ($container->has('monolog.logger.contao.error'))
			{
				$container->get('monolog.logger.contao.error')->error(sprintf('[Spielerregister] Visitenkarte "%s" (ID %s) ließ sich nicht auswerten: %s', $arrKarte['title'] ?? '', $arrKarte['id'] ?? '?', $e->getMessage()));
			}

			$strInhalt = '';
		}

		return array
		(
			'inhalt'      => $strInhalt,
			'hintergrund' => self::hintergrundUrl($arrKarte['backgroundSRC'] ?? null),
			'klasse'      => trim((string) ($arrKarte['cssClass'] ?? '')),
		);
	}

	/**
	 * Liefert die Adresse des Hintergrundbildes einer Vorlage.
	 *
	 * Die Adresse kommt aus dem Bildstudio (ohne Größe, also das Original) statt
	 * aus dem nackten Dateipfad: Contao 5 schreibt kein <base>-Tag mehr, ein
	 * relativer Pfad wie "files/…" liefe dort auf verschachtelten Seitenadressen
	 * ins Leere. Das Studio liefert je Version die passende Form — "/files/…"
	 * unter Contao 5, "files/…" unter Contao 4.13 mit <base>-Tag.
	 *
	 * @param mixed $varUuid Binäre UUID aus dem Feld backgroundSRC, oder null
	 *
	 * @return string Die Adresse des Bildes, oder ein leerer String, wenn keins
	 *                gewählt ist oder die Datei fehlt
	 */
	protected static function hintergrundUrl($varUuid): string
	{
		$strPfad = self::dateipfad($varUuid);

		if ('' === $strPfad)
		{
			return '';
		}

		$figure = System::getContainer()
			->get('contao.image.studio')
			->createFigureBuilder()
			->fromPath($strPfad)
			->buildIfResourceExists();

		return null !== $figure ? $figure->getImage()->getImageSrc() : '';
	}

	/**
	 * Prüft die Vorlage beim Speichern auf fehlerhafte Abfragen.
	 *
	 * save_callback von tl_spielerregister_cards.html. Die Vorlage wird ohne
	 * Werte durch den Token-Parser geschickt; unbekannte Platzhalter sind dabei
	 * kein Fehler, unvollständige {if}-Blöcke oder ungültige Ausdrücke schon.
	 * So fällt ein Tippfehler im Backend auf und nicht erst als leere Karte auf
	 * der Website.
	 *
	 * @param mixed $varValue Die eingegebene Vorlage
	 *
	 * @return mixed Die unveränderte Vorlage
	 *
	 * @throws \Exception Mit deutscher Meldung, die Contao am Feld anzeigt
	 */
	public static function pruefeVorlage($varValue)
	{
		try
		{
			System::getContainer()->get('contao.string.simple_token_parser')->parse((string) $varValue, array());
		}
		catch (\Throwable $e)
		{
			throw new \Exception('Die Vorlage enthält eine fehlerhafte Abfrage ({if}/{elseif}/{else}/{endif}): '.$e->getMessage());
		}

		return $varValue;
	}

	/**
	 * Sucht das Foto, das auf der Visitenkarte erscheinen soll.
	 *
	 * Reihenfolge: das Referenzbild für Visitenkarten (cardSRC), sonst das
	 * jüngste Foto aus der Dateiauswahl (multiSRC), sonst das jüngste aktive Foto
	 * der Fototabelle — dieselbe Rangfolge wie auf der Detailseite.
	 *
	 * @param array<string, mixed> $arrPerson Datensatz aus tl_spielerregister
	 *
	 * @return string Pfad relativ zum Projektverzeichnis, oder ein leerer String
	 */
	protected static function bildpfad(array $arrPerson): string
	{
		$strReferenz = self::dateipfad($arrPerson['cardSRC'] ?? null);

		if ('' !== $strReferenz)
		{
			return $strReferenz;
		}

		$arrBild = Spielerregister::Bilder($arrPerson['multiSRC'] ?? '', true);

		if (\is_array($arrBild) && !empty($arrBild['image']))
		{
			return (string) $arrBild['image'];
		}

		$objImage = Database::getInstance()
			->prepare('SELECT singleSRC FROM tl_spielerregister_images WHERE pid = ? AND active = ? ORDER BY imagedate DESC')
			->limit(1)
			->execute((int) ($arrPerson['id'] ?? 0), '1');

		return $objImage->numRows ? self::dateipfad($objImage->singleSRC) : '';
	}

	/**
	 * Wandelt eine Datei-UUID in den Pfad der Datei um.
	 *
	 * @param mixed $varUuid Binäre UUID aus einem fileTree-Feld, oder null
	 *
	 * @return string Pfad relativ zum Projektverzeichnis, oder ein leerer String,
	 *                wenn keine UUID gesetzt ist, die Datei in der
	 *                Dateiverwaltung fehlt oder es sich um einen Ordner handelt
	 */
	protected static function dateipfad($varUuid): string
	{
		if (empty($varUuid))
		{
			return '';
		}

		$objFile = FilesModel::findByUuid($varUuid);

		if (null === $objFile || 'file' !== $objFile->type || !$objFile->path)
		{
			return '';
		}

		return (string) $objFile->path;
	}
}
