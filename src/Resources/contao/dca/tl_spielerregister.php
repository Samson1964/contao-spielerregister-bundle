<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Bilder;
use Schachbulle\ContaoSpielerregisterBundle\ContaoSpielerregisterBundle;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

/**
 * Tabelle tl_spielerregister
 */
$GLOBALS['TL_DCA']['tl_spielerregister'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'ctable'                      => array('tl_spielerregister_images'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array('tl_spielerregister', 'applyAdvancedFilter'),
		),
		'onsubmit_callback' => array
		(
			array('tl_spielerregister', 'generateAlias'),
			array('tl_spielerregister', 'saveNewRecordTime')
		),
		'sql' => array
		(
			'keys' => array
			(
				'id'                 => 'primary',
				'alias'              => 'index',
				'birthday'           => 'index',
				'deathday'           => 'index',
				'honorpresident'     => 'index',
				'honormember'        => 'index',
				'honorgoldpin'       => 'index',
				'honorsilverpin'     => 'index',
				'honorgoldbadge'     => 'index',
				'honorsilverbadge'   => 'index',
				'honormedal'         => 'index',
				'honorletter'        => 'index',
				'honorplate'         => 'index'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2, // DataContainer::MODE_SORTABLE
			'fields'                  => array('alias'),
			'flag'                    => 1, // DataContainer::SORT_INITIAL_LETTER_ASC
			'panelLayout'             => 'myfilter;filter,sort;search,limit',
			'panel_callback'          => array('myfilter' => array('tl_spielerregister', 'generateAdvancedFilter')),
		),
		'label' => array
		(
			'fields'                  => array('surname1', 'firstname1', 'birthday', 'deathday', 'tstamp'),
			'format'                  => '%s, %s',
			'showColumns'             => true,
			'label_callback'          => array('tl_spielerregister', 'listRecords')
		)
		// Die Operationen stehen weiter unten, weil sie sich zwischen
		// Contao 4.13 und Contao 5 unterscheiden
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('death'),
		'default'                     => 'infobox;{namen_legend},surname1,firstname1,title,alias;{namen2_legend:hide},surname2,firstname2,surname3,firstname3,surname4,firstname4;{live_legend},birthday,birthplace,birthday_alt,death,hideLifedata;{photos_legend:hide},multiSRC;{info_legend:hide},shortinfo,links,longinfo;{link_legend:hide},wikipedia,fide_id,dewis_id,chessgames_id,chess365_id,chess_id,homepage;{star_legend},importance;{fide_legend},gm_title,gm_date,im_title,im_date,wgm_title,wgm_date,wim_title,wim_date;{dsb_legend},honorpresident,honormember,honorgoldpin,honorsilverpin,honorgoldbadge,honorsilverbadge,honorletter,honorplate,honormedal;{intern_legend:hide},intern;{active_legend},nohighlighting,active'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'death'                       => 'deathday,deathplace,deathday_alt'
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'flag'                    => 5,
			'sorting'                 => true,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'createtime' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'infobox' => array
		(
			'exclude'              => true,
			'input_field_callback' => array('tl_spielerregister', 'getInfobox')
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['title'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>16, 'tl_class'=>'w50'),
			'sql'                     => "varchar(16) NOT NULL default ''"
		),
		'surname1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['surname1'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'surname2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['surname2'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'surname3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['surname2'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'surname4' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['surname2'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'firstname1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['firstname1'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'firstname2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['firstname2'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'firstname3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['firstname2'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'firstname4' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['firstname2'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['alias'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alias', 'unique'=>false, 'maxlength'=>128, 'tl_class'=>'w50 clr'),
			'sql'                     => "varbinary(128) NOT NULL default ''"
		),
		'birthday' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['birthday'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => true,
			'flag'                    => 11,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array(Helper::class, 'getDate')
			),
			'save_callback' => array
			(
				array(Helper::class, 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		'birthday_alt' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['birthday_alt'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array(Helper::class, 'getDate')
			),
			'save_callback' => array
			(
				array(Helper::class, 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		'birthplace' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['birthplace'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'deathday' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['deathday'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => true,
			'flag'                    => 12,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array(Helper::class, 'getDate')
			),
			'save_callback' => array
			(
				array(Helper::class, 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		'deathday_alt' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['deathday_alt'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array(Helper::class, 'getDate')
			),
			'save_callback' => array
			(
				array(Helper::class, 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		'deathplace' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['deathplace'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'death' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['death'],
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'hideLifedata' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['hideLifedata'],
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => array('boolean'=>true, 'tl_class'=>'clr'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'multiSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['multiSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array
			(
				'multiple'            => true,
				'fieldType'           => 'checkbox',
				'files'               => true,
				'isGallery'           => true,
				// Der Container-Parameter ersetzt Config::get('validImageTypes'),
				// das seit Contao 4.12 überholt ist
				'extensions'          => implode(',', (array) System::getContainer()->getParameter('contao.image.valid_extensions')),
				'mandatory'           => false
			),
			'sql'                     => "blob NULL"
		),
		'shortinfo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['shortinfo'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE'),
			'explanation'             => 'insertTags',
			'sql'                     => "mediumtext NULL"
		),
		'links' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['links'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'active' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_spielerregister']['link_active'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'default'               => 1,
						'eval'                  => array
						(
							'style'             => 'width: 20px',
						)
					),
					'text' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_spielerregister']['link_text'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width:90%',
							'allowHtml'         => true
						)
					),
					'url' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_spielerregister']['link_url'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'rgxp'              => 'url',
							'tl_class'          => 'wizard',
							'style'             => 'width:90%',
							'dcaPicker'         => true,
							'addWizardClass'    => false,
						),
					),
					'target' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_spielerregister']['link_target'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'eval'                  => array
						(
							'style'             => 'width: 20px',
						)
					),
				)
			),
			'sql'                     => "blob NULL"
		),
		'longinfo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['longinfo'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE'),
			'explanation'             => 'insertTags',
			'sql'                     => "text NULL"
		),
		'wikipedia' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['wikipedia'],
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'fide_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['fide_id'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>10, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'dewis_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['dewis_id'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>10, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'chessgames_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['chessgames_id'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'chess365_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['365chess_id'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'chess_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['chess_id'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'homepage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['homepage'],
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'long clr'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'importance' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['importance'],
			'exclude'                 => true,
			'default'                 => 5,
			'search'                  => false,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 12,
			'inputType'               => 'select',
			'options'                 => array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1),
			'reference'               => &$GLOBALS['TL_LANG']['tl_spielerregister'],
			'sql'                     => "int(2) unsigned NOT NULL default '5'"
		),
		// Person hat GM-Titel der FIDE
		'gm_title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['gm_title'],
			'inputType'               => 'checkbox',
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Datum des GM-Titels
		'gm_date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['gm_date'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'flag'                    => 11,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array(Helper::class, 'getDate')
			),
			'save_callback' => array
			(
				array(Helper::class, 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		// Person hat IM-Titel der FIDE
		'im_title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['im_title'],
			'inputType'               => 'checkbox',
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Datum des IM-Titels
		'im_date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['im_date'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'flag'                    => 11,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array(Helper::class, 'getDate')
			),
			'save_callback' => array
			(
				array(Helper::class, 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		// Person hat WGM-Titel der FIDE
		'wgm_title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['wgm_title'],
			'inputType'               => 'checkbox',
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Datum des WGM-Titels
		'wgm_date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['wgm_date'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'flag'                    => 11,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array(Helper::class, 'getDate')
			),
			'save_callback' => array
			(
				array(Helper::class, 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		// Person hat WIM-Titel der FIDE
		'wim_title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['wim_title'],
			'inputType'               => 'checkbox',
			'default'                 => '',
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		// Datum des WIM-Titels
		'wim_date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['wim_date'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'flag'                    => 11,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array(Helper::class, 'getDate')
			),
			'save_callback' => array
			(
				array(Helper::class, 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		// Ehrenpräsident im Jahr xxxx
		'honorpresident' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honorpresident'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		// Ehrenmitglied im Jahr xxxx
		'honormember' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honormember'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		// Goldene Ehrennadel im Jahr xxxx
		'honorgoldpin' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honorgoldpin'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		// Silberne Ehrennadel im Jahr xxxx
		'honorsilverpin' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honorsilverpin'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		// Goldene Ehrenplakette im Jahr xxxx
		'honorgoldbadge' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honorgoldbadge'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		// Silberne Ehrenplakette im Jahr xxxx
		'honorsilverbadge' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honorsilverbadge'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		// Ehrenbrief im Jahr xxxx
		'honorletter' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honorletter'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		// Ehrenteller im Jahr xxxx
		'honorplate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honorplate'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		// Bundesmedaille im Jahr xxxx
		'honormedal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['honormedal'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50', 'rgxp' => 'digit'),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		'intern' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['intern'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE'),
			'sql'                     => "text NULL"
		),
		'nohighlighting' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['nohighlighting'],
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'active' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister']['active'],
			'toggle'                  => true, // Aktiviert den Contao-eigenen Schnellschalter in der Übersicht
			'inputType'               => 'checkbox',
			'default'                 => 1,
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
	)
);

/*
 * Operationen versionsabhängig setzen: Contao 5 kennt die Kurzschreibweise, bei
 * der Beschriftung und Symbol aus dem Kern stammen, Contao 4.13 benötigt
 * vollständige Arrays. Der Schnellschalter läuft in beiden Versionen über das
 * Contao-eigene "act=toggle"; die frühere Umsetzung über codefog/contao-haste
 * ist entfallen, weil Haste nicht für Contao 5 verfügbar ist.
 */
if (ContaoSpielerregisterBundle::isContao5())
{
	$GLOBALS['TL_DCA']['tl_spielerregister']['list']['global_operations'] = array
	(
		'export' => array
		(
			'href'                => 'key=export',
			'icon'                => 'bundles/contaospielerregister/images/image.png',
			'attributes'          => 'onclick="Backend.getScrollOffset();"'
		),
		'all'
	);

	$GLOBALS['TL_DCA']['tl_spielerregister']['list']['operations'] = array
	(
		'edit',
		'editImage' => array
		(
			'href'                => 'table=tl_spielerregister_images',
			'icon'                => 'bundles/contaospielerregister/images/image.png'
		),
		'copy',
		'delete',
		'toggle',
		'show'
	);
}
else
{
	$GLOBALS['TL_DCA']['tl_spielerregister']['list']['global_operations'] = array
	(
		'export' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister']['export'],
			'href'                => 'key=export',
			'icon'                => 'bundles/contaospielerregister/images/image.png',
			'attributes'          => 'onclick="Backend.getScrollOffset();"'
		),
		'all' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
			'href'                => 'act=select',
			'class'               => 'header_edit_all',
			'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
		)
	);

	$GLOBALS['TL_DCA']['tl_spielerregister']['list']['operations'] = array
	(
		'edit' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister']['edit'],
			'href'                => 'act=edit',
			'icon'                => 'edit.svg'
		),
		'editImage' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister']['editImage'],
			'href'                => 'table=tl_spielerregister_images',
			'icon'                => 'bundles/contaospielerregister/images/image.png'
		),
		'copy' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister']['copy'],
			'href'                => 'act=copy',
			'icon'                => 'copy.svg'
		),
		'delete' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister']['delete'],
			'href'                => 'act=delete',
			'icon'                => 'delete.svg',
			'attributes'          => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"'
		),
		'toggle' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister']['toggle'],
			'href'                => 'act=toggle&amp;field=active',
			'icon'                => 'visible.svg'
		),
		'show' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister']['show'],
			'href'                => 'act=show',
			'icon'                => 'show.svg'
		),
	);
}

/**
 * Rückruffunktionen der Tabelle tl_spielerregister.
 */
class tl_spielerregister extends Backend
{
	/**
	 * Name des Sitzungsschlüssels für den erweiterten Filter.
	 */
	private const FILTER_KEY = 'tl_registerFilter';

	/**
	 * Bereitet eine Zeile der Übersicht auf.
	 *
	 * Geburts- und Sterbetag stehen in der Datenbank als Zahl JJJJMMTT und
	 * werden hier in die deutsche Schreibweise gebracht.
	 *
	 * @param array<string, mixed> $row   Der komplette Datensatz
	 * @param string               $label Die von Contao vorbereitete Beschriftung
	 * @param DataContainer        $dc    Der aufrufende Data Container
	 * @param array<int, mixed>    $args  Die Werte der in "label.fields" genannten Spalten
	 *
	 * @return array<int, mixed> Die Spaltenwerte für die Ausgabe
	 */
	public function listRecords($row, $label, DataContainer $dc, $args)
	{
		$args[2] = Helper::getDate($args[2]); // Geburtstag von JJJJMMTT umwandeln in TT.MM.JJJJ
		$args[3] = Helper::getDate($args[3]); // Sterbetag von JJJJMMTT umwandeln in TT.MM.JJJJ

		return $args;
	}

	/**
	 * Erzeugt beim Speichern automatisch das Alias aus allen Namen.
	 *
	 * Zusammengesetzt wird aus allen vier Nach- und Vornamen, getrennt durch
	 * Bindestriche. Umlaute werden vorher ersetzt: Contao selbst tut das seit
	 * Version 4.4 nicht mehr zwingend, im Register sind die Aliasse aber seit
	 * jeher umlautfrei und dienen als Bestandteil der URLs.
	 *
	 * Das Alias wird direkt per SQL geschrieben und nicht über den Data
	 * Container, weil es kein Eingabefeld der Palette ist.
	 *
	 * @param DataContainer $dc Der aufrufende Data Container
	 *
	 * @return void
	 */
	public function generateAlias(DataContainer $dc)
	{
		static $suchen = array('Ä', 'Ö', 'Ü', 'ß', 'é', 'ä', 'ö', 'ü');
		static $ersetzen = array('ae', 'oe', 'ue', 'ss', 'e', 'ae', 'oe', 'ue');

		if (null === $dc->activeRecord)
		{
			return;
		}

		$temp = $dc->activeRecord->surname1;

		foreach (array('surname2', 'surname3', 'surname4', 'firstname1', 'firstname2', 'firstname3', 'firstname4') as $strFeld)
		{
			if ($dc->activeRecord->$strFeld)
			{
				$temp .= '-'.$dc->activeRecord->$strFeld;
			}
		}

		$temp = str_replace($suchen, $ersetzen, StringUtil::generateAlias($temp));

		Database::getInstance()
			->prepare("UPDATE tl_spielerregister SET alias=? WHERE id=?")
			->execute($temp, $dc->id);
	}

	/**
	 * Vermerkt bei neuen Datensätzen den Zeitpunkt der Anlage.
	 *
	 * Der Wert wird vom Cronjob "Neue Einträge" ausgewertet und darf deshalb
	 * bei späteren Änderungen nicht überschrieben werden.
	 *
	 * @param DataContainer $dc Der aufrufende Data Container
	 *
	 * @return void
	 */
	public function saveNewRecordTime(DataContainer $dc)
	{
		if (null === $dc->activeRecord || $dc->activeRecord->createtime)
		{
			return;
		}

		Database::getInstance()
			->prepare("UPDATE tl_spielerregister SET createtime=? WHERE id=?")
			->execute(time(), $dc->id);
	}

	/**
	 * Erzeugt die Infobox oberhalb des Eingabeformulars.
	 *
	 * Die Box enthält Suchlinks zu den gängigen Schachdatenbanken und den
	 * Zugang zu den Fotos der Person. Die vorhandenen Fotos werden als kleine
	 * Vorschaubilder angezeigt, damit sich der Bestand auf einen Blick erfassen
	 * lässt.
	 *
	 * @param DataContainer $dc Der aufrufende Data Container
	 *
	 * @return string Das fertige HTML der Infobox
	 */
	public function getInfobox(DataContainer $dc)
	{
		if (null === $dc->activeRecord)
		{
			return '';
		}

		$strVorname = rawurlencode((string) $dc->activeRecord->firstname1);
		$strNachname = rawurlencode((string) $dc->activeRecord->surname1);

		// Suchmaschinenlinks generieren
		$googlelink = 'https://www.google.de/search?q=%22'.$strVorname.'+'.$strNachname.'%22+schach';
		$chess365link = 'https://www.365chess.com/search_result.php?wlname='.$strNachname.'&amp;wname='.$strVorname.'&amp;nocolor=on&amp;sply=1&amp;submit_search=1';
		$chessgameslink = 'https://www.chessgames.com/perl/ezsearch.pl?search='.$strNachname;
		$chesslink = 'https://www.chess.com/games/search?sort=6&amp;p1='.$strNachname.'+'.$strVorname;

		// Link auf die Fotos der Person; die Backend-Adresse und der aktuelle
		// Modulaufruf stecken schon in der URL, deshalb reicht addToUrl()
		$popuplink = $this->addToUrl('table=tl_spielerregister_images&amp;id='.$dc->activeRecord->id.'&amp;popup=1');
		$strTitel = StringUtil::specialchars(sprintf('Fotos von Datensatz %s bearbeiten', $dc->activeRecord->id));

		$popuplink .= '" title="'.$strTitel.'" onclick="Backend.openModalIframe({\'width\':840,\'title\':\''.$strTitel.'\',\'url\':this.href});return false';

		// Vorhandene Bilder anzeigen
		$bilder = '';

		$objImages = Database::getInstance()
			->prepare('SELECT singleSRC FROM tl_spielerregister_images WHERE pid = ?')
			->execute($dc->activeRecord->id);

		while ($objImages->next())
		{
			$strThumbnail = Bilder::vorschaubildAusUuid($objImages->singleSRC, 20, 20);

			if ('' !== $strThumbnail)
			{
				$bilder .= '<img src="'.$strThumbnail.'" alt=""> ';
			}
		}

		// Fotolink generieren
		if ($bilder)
		{
			$fotolink = 'Fotos bearbeiten: <a href="'.$popuplink.'">'.$bilder.'</a>';
		}
		else
		{
			$fotolink = '<a href="'.$popuplink.'">Fotos bearbeiten</a>';
		}

		return '
<div class="widget long">
  <h3><label>Infobox</label></h3>
  <p>Spieler suchen in: <a href="'.$googlelink.'" target="_blank" rel="noreferrer noopener">Google</a> |
  <a href="'.$chess365link.'" target="_blank" rel="noreferrer noopener">365chess.com</a> |
  <a href="'.$chessgameslink.'" target="_blank" rel="noreferrer noopener">chessgames.com</a> |
  <a href="'.$chesslink.'" target="_blank" rel="noreferrer noopener">chess.com</a></p>
  <p>'.$fotolink.'</p>
</div>';
	}

	/**
	 * Erzeugt den zusätzlichen Filter über dem Suchpanel.
	 *
	 * Der Filter deckt Lücken im Datenbestand auf, etwa fehlende Geburtsdaten
	 * oder unvollständige Datumsangaben. Contao-eigene Filter können das nicht
	 * leisten, weil sie nur auf einzelne Spaltenwerte prüfen.
	 *
	 * @param DataContainer $dc Der aufrufende Data Container
	 *
	 * @return string Das HTML des Auswahlfeldes; leer, solange ein einzelner
	 *                Datensatz bearbeitet wird
	 */
	public function generateAdvancedFilter(DataContainer $dc)
	{
		if (Input::get('id') > 0)
		{
			return '';
		}

		$session = self::getFilterSession();

		// Filter
		$arrFilters = array
		(
			'spr_filter' => array
			(
				'name'    => 'spr_filter',
				'label'   => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_extended'] ?? 'Erweiterter Filter',
				'options' => array
				(
					'10' => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_roundbirthdays'] ?? '',
					'1'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_over100'] ?? '',
					'2'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_birthdayfail'] ?? '',
					'3'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_deathdayfail'] ?? '',
					'4'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_birthdayerror'] ?? '',
					'5'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_deathdayerror'] ?? '',
					'8'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_birthplacefail'] ?? '',
					'9'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_deathplacefail'] ?? '',
					'6'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_shortinfo'] ?? '',
					'7'  => $GLOBALS['TL_LANG']['tl_spielerregister']['filter_firstnamefail'] ?? '',
				)
			),
		);

		$strBuffer = '<div class="tl_filter spr_filter tl_subpanel"><strong>'.($GLOBALS['TL_LANG']['tl_spielerregister']['filter'] ?? 'Filter').':</strong>'."\n";

		// Auswahlfelder erzeugen
		foreach ($arrFilters as $arrFilter)
		{
			$strOptions = '
			<option value="'.$arrFilter['name'].'">'.$arrFilter['label'].'</option>
			<option value="'.$arrFilter['name'].'">---</option>'."\n";

			foreach ($arrFilter['options'] as $k => $v)
			{
				$blnAktiv = isset($session[$arrFilter['name']]) && $session[$arrFilter['name']] === (string) $k;
				$strOptions .= '  <option value="'.$k.'"'.($blnAktiv ? ' selected' : '').'>'.$v.'</option>'."\n";
			}

			$strBuffer .= '<select name="'.$arrFilter['name'].'" id="'.$arrFilter['name'].'" class="tl_select'.(isset($session[$arrFilter['name']]) ? ' active' : '').'">'.$strOptions.'</select>'."\n";
		}

		return $strBuffer.'</div>';
	}

	/**
	 * Wendet den zusätzlichen Filter auf die Übersicht an.
	 *
	 * Die Auswahl wird in der Backend-Sitzung gemerkt und beim nächsten Aufruf
	 * wieder angewendet. Technisch wird die Liste der erlaubten IDs in
	 * list.sorting.root geschrieben; Contao zeigt dann nur noch diese
	 * Datensätze an.
	 *
	 * @return void
	 */
	public function applyAdvancedFilter()
	{
		$session = self::getFilterSession();

		// Auswahl aus dem abgeschickten Formular übernehmen
		foreach (array_keys($_POST) as $k)
		{
			if (strncmp($k, 'spr_', 4) !== 0)
			{
				continue;
			}

			if ($k === Input::post($k))
			{
				unset($session[$k]); // Filter zurücksetzen
			}
			else
			{
				$session[$k] = Input::post($k);
			}
		}

		self::setFilterSession($session);

		if (Input::get('id') > 0 || !isset($session['spr_filter']))
		{
			return;
		}

		$objPlayers = null;

		switch ($session['spr_filter'])
		{
			case '1': // Älter als 100 Jahre, nicht verstorben
				$hundertjahre = (int) date('Ymd') - 1000000; // Aktuelles Datum minus 100 Jahre
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE birthday <= ? AND birthday != ? AND death = ?")
					->execute($hundertjahre, 0, '');
				break;

			case '2': // Geburtsdatum fehlt
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE birthday = ?")
					->execute(0);
				break;

			case '3': // Sterbedatum fehlt
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE deathday = ? AND death = ?")
					->execute(0, 1);
				break;

			case '4': // Geburtsdatum unvollständig
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE birthday % 100 = ? AND birthday != ?")
					->execute(0, 0);
				break;

			case '5': // Sterbedatum unvollständig
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE deathday % 100 = ? AND deathday != ? AND death = ?")
					->execute(0, 0, 1);
				break;

			case '6': // Kurzinfo fehlt
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE shortinfo = ?")
					->execute('');
				break;

			case '7': // Vorname fehlt
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE firstname1 = ?")
					->execute('');
				break;

			case '8': // Geburtsort fehlt
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE birthplace = ?")
					->execute('');
				break;

			case '9': // Sterbeort fehlt
				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE deathplace = ? AND death = ?")
					->execute('', 1);
				break;

			case '10': // Runde Geburtstage lebender Personen im aktuellen Jahr
				$jahr = (int) date('Y');
				$arrJahre = array();

				foreach (array(30, 40, 50, 60, 65, 70, 75, 80, 85, 90, 95, 100) as $intAlter)
				{
					$arrJahre[] = $jahr - $intAlter;
				}

				// Ein Fragezeichen je Jahrgang, damit die Werte gebunden bleiben
				$strPlatzhalter = implode(' OR ', array_fill(0, \count($arrJahre), 'SUBSTR(birthday,1,4) = ?'));

				$objPlayers = Database::getInstance()
					->prepare("SELECT id FROM tl_spielerregister WHERE (".$strPlatzhalter.") AND death = ?")
					->execute(...array_merge($arrJahre, array('')));
				break;

			default:
				return;
		}

		$arrPlayers = $objPlayers->fetchEach('id');

		// Ein leeres Ergebnis muss als "keine Treffer" ankommen; ein leeres
		// Array würde Contao dazu bringen, alle Datensätze anzuzeigen
		if (!$arrPlayers)
		{
			$arrPlayers = array(0);
		}

		$GLOBALS['TL_DCA']['tl_spielerregister']['list']['sorting']['root'] = $arrPlayers;
	}

	/**
	 * Liest die gemerkte Filterauswahl aus der Backend-Sitzung.
	 *
	 * Die Klasse Session, über die das früher lief, gibt es in Contao 5 nicht
	 * mehr; gelesen wird deshalb direkt der Sitzungsbehälter des Backends.
	 *
	 * @return array<string, string> Die gemerkten Filterwerte
	 */
	private static function getFilterSession(): array
	{
		$objBag = self::getSessionBag();

		if (null === $objBag)
		{
			return array();
		}

		$arrFilter = $objBag->get('filter');

		return \is_array($arrFilter) && isset($arrFilter[self::FILTER_KEY]) && \is_array($arrFilter[self::FILTER_KEY])
			? $arrFilter[self::FILTER_KEY]
			: array();
	}

	/**
	 * Schreibt die Filterauswahl in die Backend-Sitzung zurück.
	 *
	 * @param array<string, string> $arrWerte Die zu merkenden Filterwerte
	 *
	 * @return void
	 */
	private static function setFilterSession(array $arrWerte): void
	{
		$objBag = self::getSessionBag();

		if (null === $objBag)
		{
			return;
		}

		$arrFilter = $objBag->get('filter');
		$arrFilter = \is_array($arrFilter) ? $arrFilter : array();
		$arrFilter[self::FILTER_KEY] = $arrWerte;

		$objBag->set('filter', $arrFilter);
	}

	/**
	 * Liefert den Sitzungsbehälter des Backends.
	 *
	 * Ohne laufende Sitzung – etwa beim Aufruf über die Kommandozeile – gibt es
	 * nichts zu merken; die Rückgabe ist dann null und die Filterfunktionen
	 * arbeiten ohne gespeicherte Auswahl weiter.
	 *
	 * Contao legt den Behälter als ArrayAttributeBag an. Die allgemeine
	 * Schnittstelle SessionBagInterface kennt weder get() noch set(), deshalb
	 * wird hier ausdrücklich auf AttributeBagInterface geprüft – sonst hinge der
	 * Zugriff daran, dass Contao den Behältertyp nie ändert.
	 *
	 * @return AttributeBagInterface|null Der Behälter, oder null ohne Sitzung
	 */
	private static function getSessionBag(): ?AttributeBagInterface
	{
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();

		if (null === $request || !$request->hasSession())
		{
			return null;
		}

		$objBag = $request->getSession()->getBag('contao_backend');

		return $objBag instanceof AttributeBagInterface ? $objBag : null;
	}
}
