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
		else
		{
			return false; // Tag nicht dabei
		}

	}

	function getLink($id)
	{
		return 'person/player/'.$id.'.html';
	}
}
