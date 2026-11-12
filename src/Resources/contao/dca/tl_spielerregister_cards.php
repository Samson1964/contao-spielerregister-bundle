<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\BackendUser;
use Contao\DC_Table;
use Contao\System;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Visitenkarte;
use Schachbulle\ContaoSpielerregisterBundle\ContaoSpielerregisterBundle;

/**
 * Tabelle tl_spielerregister_cards: Vorlagen für Visitenkarten
 */
$GLOBALS['TL_DCA']['tl_spielerregister_cards'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		// Die Tabelle hängt an keiner Elterntabelle; Contao 4.13 und 5 zeigen
		// damit selbst die Schaltfläche „Zurück" zur Personenliste an
		'backlink'                    => 'do=spielerregister',
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1, // DataContainer::MODE_SORTED
			'fields'                  => array('title'),
			'flag'                    => 1, // DataContainer::SORT_INITIAL_LETTER_ASC
			'panelLayout'             => 'search,limit'
		),
		'label' => array
		(
			'fields'                  => array('title'),
			'format'                  => '%s'
		)
		// Die Operationen stehen weiter unten, weil sie sich zwischen
		// Contao 4.13 und Contao 5 unterscheiden
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{title_legend},title;{html_legend},html;{image_legend},size,floating,fullsize;{layout_legend},backgroundSRC,cssClass'
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
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'sql'                     => "varchar(128) NOT NULL default ''"
		),
		'html' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['html'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			// Hilfe hinter dem Fragezeichen: languages/de/explain.php
			'explanation'             => 'spielerregister_visitenkarte',
			// Wie beim HTML-Inhaltselement des Kerns; preserveTags lässt
			// Insert-Tags und {if}-Abfragen unangetastet
			'eval'                    => array('mandatory'=>true, 'allowHtml'=>true, 'preserveTags'=>true, 'class'=>'monospace', 'rte'=>'ace|html', 'helpwizard'=>true, 'tl_class'=>'clr'),
			'save_callback'           => array
			(
				array(Visitenkarte::class, 'pruefeVorlage')
			),
			'sql'                     => "mediumtext NULL"
		),
		'size' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['size'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('rgxp'=>'natural', 'includeBlankOption'=>true, 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
			'options_callback'        => static function ()
			{
				return System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance());
			},
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'floating' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['floating'],
			'exclude'                 => true,
			'inputType'               => 'radioTable',
			'options'                 => array('above', 'left', 'right', 'below'),
			'eval'                    => array('cols'=>4, 'tl_class'=>'w50'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'sql'                     => "varchar(32) NOT NULL default 'above'"
		),
		'fullsize' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['fullsize'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'backgroundSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['backgroundSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array
			(
				'fieldType'           => 'radio',
				'filesOnly'           => true,
				'extensions'          => implode(',', (array) System::getContainer()->getParameter('contao.image.valid_extensions')),
				'tl_class'            => 'clr'
			),
			'sql'                     => "binary(16) NULL"
		),
		'cssClass' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['cssClass'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
	)
);

/*
 * Operationen versionsabhängig setzen (siehe tl_spielerregister.php). Der
 * Rücksprung zur Personenliste kommt aus config.backlink.
 */
if (ContaoSpielerregisterBundle::isContao5())
{
	$GLOBALS['TL_DCA']['tl_spielerregister_cards']['list']['global_operations'] = array
	(
		'all'
	);

	$GLOBALS['TL_DCA']['tl_spielerregister_cards']['list']['operations'] = array
	(
		'edit',
		'copy',
		'delete',
		'show'
	);
}
else
{
	$GLOBALS['TL_DCA']['tl_spielerregister_cards']['list']['global_operations'] = array
	(
		'all' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
			'href'                => 'act=select',
			'class'               => 'header_edit_all',
			'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
		)
	);

	$GLOBALS['TL_DCA']['tl_spielerregister_cards']['list']['operations'] = array
	(
		'edit' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['edit'],
			'href'                => 'act=edit',
			'icon'                => 'edit.svg'
		),
		'copy' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['copy'],
			'href'                => 'act=copy',
			'icon'                => 'copy.svg'
		),
		'delete' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['delete'],
			'href'                => 'act=delete',
			'icon'                => 'delete.svg',
			'attributes'          => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"'
		),
		'show' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_cards']['show'],
			'href'                => 'act=show',
			'icon'                => 'show.svg'
		)
	);
}
