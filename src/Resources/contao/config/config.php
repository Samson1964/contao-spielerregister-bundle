<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\ArrayUtil;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Import;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Tags;
use Schachbulle\ContaoSpielerregisterBundle\ContentElements\Person;
use Schachbulle\ContaoSpielerregisterBundle\Klassen\Spielerregister;
use Schachbulle\ContaoSpielerregisterBundle\Module\DeathList;
use Schachbulle\ContaoSpielerregisterBundle\Module\Export;
use Schachbulle\ContaoSpielerregisterBundle\Module\HonorList;
use Schachbulle\ContaoSpielerregisterBundle\Module\PlayerDetail;
use Schachbulle\ContaoSpielerregisterBundle\Module\TitleList;
use Schachbulle\ContaoSpielerregisterBundle\Module\Visitenkarte;
use Schachbulle\ContaoSpielerregisterBundle\Module\Yearday;
use Schachbulle\ContaoSpielerregisterBundle\Module\YeardayList;

/*
 * Frontend-Module des Spielerregisters
 *
 * Die Registrierung über $GLOBALS['FE_MOD'] funktioniert in Contao 4.13 und
 * Contao 5 gleichermaßen; die Klassen erben weiterhin von Contao\Module.
 */
$GLOBALS['FE_MOD']['spielerregister'] = array
(
	'spielerregister_playerdetail' => PlayerDetail::class,
	'spielerregister_yeardaylist'  => YeardayList::class,
	'spielerregister_yearday'      => Yearday::class,
	'spielerregister_honorlist'    => HonorList::class,
	'spielerregister_deathlist'    => DeathList::class,
	'spielerregister_titlelist'    => TitleList::class,
	'spielerregister_visitenkarte' => Visitenkarte::class,
);

/*
 * Backend-Modul des Spielerregisters in das Backend-Menü "Inhalte" an Position 1
 * einfügen. ArrayUtil::arrayInsert() ersetzt die in Contao 5 entfallene globale
 * Funktion array_insert().
 *
 * tl_spielerregister_cards (Visitenkarten) hängt an keiner Elterntabelle; sie
 * muss trotzdem hier stehen, sonst lässt Contao den Aufruf über table= nicht zu.
 */
ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['content'], 1, array
(
	'spielerregister' => array
	(
		'tables'         => array('tl_spielerregister', 'tl_spielerregister_images', 'tl_spielerregister_cards'),
		'icon'           => 'bundles/contaospielerregister/images/icon.png',
		'import'         => array(Import::class, 'importSpieler'),
		'export'         => array(Export::class, 'exportSpieler')
	)
));

/*
 * Inhaltselemente
 */
$GLOBALS['TL_CTE']['includes']['spielerregister_person'] = Person::class;

/*
 * Insert-Tags anmelden
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array(Spielerregister::class, 'ReplacePlayer');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array(Tags::class, 'Register');

/*
 * Voreinstellungen
 *
 * Anzahl der E-Mails je Durchgang und Wartezeit zwischen zwei Durchgängen des
 * Jahrestags-Newsletters; einstellbar unter System -> Einstellungen.
 */
$GLOBALS['TL_CONFIG']['spielerregister_zyklus'] = 30;
$GLOBALS['TL_CONFIG']['spielerregister_wartezeit'] = 0;
