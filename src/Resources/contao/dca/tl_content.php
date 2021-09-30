<?php

/**
 * Paletten
 */
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'championslist_filter';
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielerregister_person'] = '{type_legend},type,headline;{spielerregister_legend},spielerregister_id;{image_legend},addImage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';
//
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['championslist_filter'] = 'championsfrom,championsto';

/**
 * Felder
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['spielerregister_id'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['spielerregister_id'],
	'exclude'                 => true,
	'options_callback'        => array('\Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper', 'getRegister'),
	'inputType'               => 'select',
	'eval'                    => array
	(
		'mandatory'           => false,
		'multiple'            => false,
		'chosen'              => true,
		'submitOnChange'      => false,
		'includeBlankOption'  => true,
		'tl_class'            => 'long',
		'eval'                => array
		(
			'mandatory'       => true
		)
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

//$GLOBALS['TL_DCA']['tl_content']['fields']['spielerregister_overwriteHeadline'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['spielerregister_overwriteHeadline'],
//	'exclude'                 => true,
//	'inputType'               => 'checkbox',
//	'eval'                    => array('tl_class'=>'w50 m12'),
//	'sql'                     => "char(1) NOT NULL default ''"
//);

/*****************************************
 * Klasse tl_content_spielerregister
 *****************************************/

class tl_content_spielerregister extends \Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

}
