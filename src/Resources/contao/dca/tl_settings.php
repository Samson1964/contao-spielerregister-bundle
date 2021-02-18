<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   contao-spielerregister-bundle
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2013
 */

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{spielerregister_legend:hide},spielerregister_imageSize,spielerregister_newsletter';

/**
 * fields
 */

$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_imageSize'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_imageSize'],
	'exclude'                 => true,
	'inputType'               => 'imageSize',
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array
	(
		'rgxp'                => 'natural',
		'includeBlankOption'  => true,
		'nospace'             => true,
		'helpwizard'          => true,
		'tl_class'            => 'w50'
	),
	'options_callback'        => static function ()
	{
		return System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(BackendUser::getInstance());
	},
	'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_newsletter'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_newsletter'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_settings_spielerregister', 'Newsletterverteiler'),
	'eval'                    => array
	(
		'includeBlankOption'  => true,
		'tl_class'            => 'w50'
	),
	'sql'                     => "int(10) NOT NULL default ''"
);

class tl_settings_spielerregister
{
	/**
	 * options_callback: Erm�glicht das Bef�llen eines Drop-Down-Men�s oder einer Checkbox-Liste mittels einer individuellen Funktion.
	 * @param  $dc
	 * @return array
	 */
	public function Newsletterverteiler(DataContainer $dc)
	{
		$optionen = array();
		$objNewsletter = \Database::getInstance()->prepare("SELECT * FROM tl_newsletter_channel")
		                                         ->execute();
		if($objNewsletter->numRows)
		{
			while($objNewsletter->next())
			{
				$optionen[$objNewsletter->id] = $objNewsletter->title;
			}
		}
		return $optionen;
	}
}
