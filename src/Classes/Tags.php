<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   fh-counter
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Classes;

class Tags extends \Frontend
{

	public function Register($strTag)
	{
		$arrSplit = explode('::', $strTag);

		// Inserttag {{spielerregister_url::ID}}
		// Liefert zu einer ID den Link zum Frontendeintrag des Spielers
		if($arrSplit[0] == 'spielerregister_url' || $arrSplit[0] == 'cache_spielerregister_url')
		{
			// Parameter angegeben?
			if(isset($arrSplit[1]))
			{
				return self::getLink($arrSplit[1]);
			}
			else
			{
				return 'ID fehlt!';
			}
		}
		elseif($arrSplit[0] == 'spielerregister_box' || $arrSplit[0] == 'cache_spielerregister_box')
		{
			// Parameter angegeben?
			if(isset($arrSplit[1]))
			{
				// Template-Objekt erzeugen
				$this->Template = new \FrontendTemplate('spielerregister_modalbox');

				$content_id = self::generateRandomString(); // Zufallswert erzeugen für Dialogbox

				// Spielerregister-ID abfragen und Datensatz laden
				$objPlayer = \Database::getInstance()->prepare("SELECT * FROM tl_spielerregister WHERE id=?")
				                                     ->execute($arrSplit[1]);

				$this->Template->player_name = $objPlayer->firstname1.' '.$objPlayer->surname1;
				$this->Template->player_info = $objPlayer->shortinfo;
				$this->Template->contentid = $content_id;
				$this->Template->player_leben = \Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper::getLebensdaten($objPlayer->birthday, $objPlayer->birthplace, $objPlayer->death, $objPlayer->deathday, $objPlayer->deathplace);
				$this->Template->linkname = $arrSplit[2] ? $arrSplit[2] : $objPlayer->firstname1.' '.$objPlayer->surname1;

				// Geparstes Template zurückgeben
				return $this->Template->parse();
			}
			else
			{
				return 'ID fehlt!';
			}
		}
		else
		{
			return false; // Tag nicht dabei
		}

	}

	/**
	 * Liefert die URL zur Detailseite zurück
	 * @param     $id    int        ID des Datensatzes der Person
	 * @return           string     URL ohne Domain
	 */
	function getLink($id)
	{
		if($GLOBALS['TL_CONFIG']['spielerregister_detailseite'])
		{
			$pageModel = \PageModel::findByPK($GLOBALS['TL_CONFIG']['spielerregister_detailseite']);
		
			if($pageModel)
			{
				$url = \Controller::generateFrontendUrl($pageModel->row(), '/player/'.$id);
				return $url;
			}
		}

		return false;

	}

	function generateRandomString($length = 10) 
	{
		srand ((double)microtime()*1000000);
		$zufall = rand();
		$zufallsstring = substr(md5($zufall), 0, $length);
		return $zufallsstring; 
	}

}
