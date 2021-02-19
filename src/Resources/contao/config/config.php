<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   bdf
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

/**
 * Frontend-Module des Spielerregisters
 */
$GLOBALS['FE_MOD']['spielerregister'] = array
(
	'spielerregister_playerdetail' => 'Schachbulle\ContaoSpielerregisterBundle\Module\PlayerDetail',
	'spielerregister_yeardaylist'  => 'Schachbulle\ContaoSpielerregisterBundle\Module\YeardayList',
	'spielerregister_yearday'      => 'Schachbulle\ContaoSpielerregisterBundle\Module\Yearday',
	'spielerregister_honorlist'    => 'Schachbulle\ContaoSpielerregisterBundle\Module\HonorList',
	'spielerregister_deathlist'    => 'Schachbulle\ContaoSpielerregisterBundle\Module\DeathList',
	'spielerregister_titlelist'    => 'Schachbulle\ContaoSpielerregisterBundle\Module\TitleList',
);

/**
 * Backend-Module des Spielerregisters in das Backend-Menü "Inhalte" an Position 1 einfügen
 */
array_insert($GLOBALS['BE_MOD']['content'], 1, array
(
	'spielerregister' => array
	(
		'tables'         => array('tl_spielerregister', 'tl_spielerregister_images'),
		'icon'           => 'bundles/contaospielerregister/images/icon.png',
		'export'         => array('Schachbulle\ContaoSpielerregisterBundle\Module\Export', 'exportSpieler')
	)
));

/**
 * Inserttag für Registerersetzung in den Hooks anmelden
 */

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Schachbulle\ContaoSpielerregisterBundle\Klassen\Spielerregister','ReplacePlayer');

/**
 * Voreinstellungen
 */

$GLOBALS['TL_CONFIG']['spielerregister_zyklus'] = 30;
$GLOBALS['TL_CONFIG']['spielerregister_wartezeit'] = 0;
