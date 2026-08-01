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
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/**
 * Insert-Tags des Spielerregisters.
 *
 * Bereitgestellt werden {{spielerregister_url::ID}} für den reinen Link zur
 * Detailseite und {{spielerregister_box::ID::Text}} für einen Textlink, der
 * eine Modalbox mit den Lebensdaten der Person öffnet.
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
