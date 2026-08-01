<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;

/*
 * Palette des Inhaltselements "Person aus dem Spielerregister"
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielerregister_person'] = '{type_legend},type,headline;{spielerregister_legend},spielerregister_id;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

/*
 * Felder
 */

// Auswahl der Person aus dem Register
$GLOBALS['TL_DCA']['tl_content']['fields']['spielerregister_id'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['spielerregister_id'],
	'exclude'                 => true,
	'options_callback'        => array(Helper::class, 'getRegister'),
	'inputType'               => 'select',
	'eval'                    => array
	(
		'mandatory'           => false,
		'multiple'            => false,
		'chosen'              => true,
		'submitOnChange'      => false,
		'includeBlankOption'  => true,
		'tl_class'            => 'long'
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);
