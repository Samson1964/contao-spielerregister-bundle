<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Classes;

use Contao\Database;
use Contao\Frontend;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Insert-Tags des Spielerregisters.
 *
 * Bereitgestellt werden {{spielerregister_url::ID}} für die reine Adresse der
 * Detailseite, {{spielerregister_link::ID::Text}} für einen fertigen Link
 * dorthin und {{spielerregister_box::ID::Text}} für einen Textlink, der eine
 * Modalbox mit den Lebensdaten der Person öffnet.
 */
class Tags extends Frontend
{
	/**
	 * Ersetzt die Insert-Tags dieser Erweiterung.
	 *
	 * Wird über den Hook replaceInsertTags aufgerufen. Contao ruft alle
	 * angemeldeten Hooks der Reihe nach auf, bis einer etwas anderes als false
	 * zurückgibt; fremde Insert-Tags müssen deshalb mit false beantwortet
	 * werden.
	 *
	 * @param string $strTag Der Insert-Tag ohne die geschweiften Klammern
	 *
	 * @return string|false Die Ersetzung, oder false wenn der Tag nicht zu
	 *                      dieser Erweiterung gehört
	 */
	public function Register($strTag)
	{
		$arrSplit = explode('::', (string) $strTag);

		// Inserttag {{spielerregister_url::ID}}
		// Liefert zu einer ID den Link zum Frontendeintrag des Spielers
		if ($arrSplit[0] == 'spielerregister_url' || $arrSplit[0] == 'cache_spielerregister_url')
		{
			if (!isset($arrSplit[1]))
			{
				return 'ID fehlt!';
			}

			return self::getLink($arrSplit[1]);
		}

		// Inserttag {{spielerregister_link::ID}} bzw. {{spielerregister_link::ID::Linktext}}
		// Liefert einen fertigen Link mit dem Namen zur Detailseite der Person
		if ($arrSplit[0] == 'spielerregister_link' || $arrSplit[0] == 'cache_spielerregister_link')
		{
			if (!isset($arrSplit[1]))
			{
				return 'ID fehlt!';
			}

			return self::erzeugeLink($arrSplit[1], $arrSplit[2] ?? '');
		}

		// Inserttag {{spielerregister_box::ID::Linktext}}
		// Liefert einen Textlink, der eine Modalbox mit den Daten öffnet
		if ($arrSplit[0] == 'spielerregister_box' || $arrSplit[0] == 'cache_spielerregister_box')
		{
			if (!isset($arrSplit[1]))
			{
				return 'ID fehlt!';
			}

			// Spielerregister-ID abfragen und Datensatz laden
			$objPlayer = Database::getInstance()
				->prepare("SELECT * FROM tl_spielerregister WHERE id=?")
				->execute($arrSplit[1]);

			if (!$objPlayer->numRows)
			{
				// Ohne Datensatz gibt es nichts anzuzeigen; ein selbst gesetzter
				// Linktext wird trotzdem ausgegeben, damit der Satz lesbar bleibt
				return $arrSplit[2] ?? '';
			}

			// Template-Objekt erzeugen
			$objTemplate = new FrontendTemplate('spielerregister_modalbox');

			$objTemplate->player_name = trim($objPlayer->firstname1.' '.$objPlayer->surname1);
			$objTemplate->player_info = $objPlayer->shortinfo;
			$objTemplate->contentid = self::generateRandomString(); // Zufallswert für die Dialogbox
			$objTemplate->player_leben = $objPlayer->hideLifedata ? '' : Helper::getLebensdaten($objPlayer->birthday, $objPlayer->birthplace, $objPlayer->death, $objPlayer->deathday, $objPlayer->deathplace);
			$objTemplate->linkname = ($arrSplit[2] ?? '') !== '' ? $arrSplit[2] : trim($objPlayer->firstname1.' '.$objPlayer->surname1);

			// Geparstes Template zurückgeben
			return $objTemplate->parse();
		}

		return false; // Tag nicht dabei
	}

	/**
	 * Liefert die URL zur Detailseite einer Person.
	 *
	 * Die Detailseite steht unter System -> Einstellungen; die Auflösung
	 * übernimmt der Helper, damit beide Insert-Tags und die Module denselben
	 * Weg benutzen.
	 *
	 * @param int|string $id ID des Datensatzes der Person
	 *
	 * @return string URL ohne Domain, oder ein leerer String wenn keine
	 *                Detailseite eingestellt ist
	 */
	public static function getLink($id): string
	{
		return Helper::getPlayerlink($id);
	}

	/**
	 * Erzeugt einen Textlink auf die Detailseite einer Person.
	 *
	 * Ergebnis ist <a href="…" class="spielerregister_link">Vorname Nachname</a>,
	 * mit eigenem Linktext statt des Namens, falls einer angegeben ist. Verlinkt
	 * wird nur, was die Detailseite auch anzeigt: aktive Personen mit einer
	 * Bedeutung von mindestens 1. Fehlt die Person, ist sie deaktiviert oder
	 * ist keine Detailseite eingestellt, bleibt nur der Text stehen — so bricht
	 * kein Satz ab und es entsteht kein toter Link.
	 *
	 * @param int|string $id      ID des Datensatzes der Person
	 * @param string     $strText Eigener Linktext; leer bedeutet den Namen der Person
	 *
	 * @return string Der Link, nur der Text, oder ein leerer String, wenn es
	 *                weder Person noch eigenen Text gibt
	 */
	public static function erzeugeLink($id, string $strText = ''): string
	{
		$objPerson = Database::getInstance()
			->prepare('SELECT id, firstname1, surname1, active, importance FROM tl_spielerregister WHERE id = ?')
			->limit(1)
			->execute((int) $id);

		$strName = $objPerson->numRows ? trim($objPerson->firstname1.' '.$objPerson->surname1) : '';
		$strAnzeige = '' !== $strText ? $strText : StringUtil::specialchars($strName);

		if (!$objPerson->numRows || !$objPerson->active || (int) $objPerson->importance < 1)
		{
			return $strAnzeige;
		}

		$strUrl = self::getLink($objPerson->id);

		if ('' === $strUrl)
		{
			return $strAnzeige;
		}

		return sprintf('<a href="%s" class="spielerregister_link" title="%s">%s</a>', StringUtil::specialchars($strUrl), StringUtil::specialchars($strName), $strAnzeige);
	}

	/**
	 * Erzeugt eine zufällige Zeichenkette aus Hexadezimalziffern.
	 *
	 * Der Wert dient nur dazu, die IDs mehrerer Modalboxen auf derselben Seite
	 * auseinanderzuhalten; er muss weder unvorhersehbar noch eindeutig im
	 * kryptografischen Sinn sein.
	 *
	 * @param int $length Gewünschte Länge der Zeichenkette
	 *
	 * @return string Zeichenkette aus 0-9 und a-f
	 */
	public static function generateRandomString(int $length = 10): string
	{
		return substr(md5(uniqid('', true)), 0, $length);
	}
}
