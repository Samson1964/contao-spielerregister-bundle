<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\DC_Table;
use Contao\FilesModel;
use Schachbulle\ContaoSpielerregisterBundle\Classes\Bilder;
use Schachbulle\ContaoSpielerregisterBundle\ContaoSpielerregisterBundle;

/**
 * Tabelle tl_spielerregister_images
 */
$GLOBALS['TL_DCA']['tl_spielerregister_images'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'ptable'                      => 'tl_spielerregister',
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid' => 'index',
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4, // DataContainer::MODE_PARENT
			'fields'                  => array('imagedate DESC'),
			'headerFields'            => array('surname1', 'firstname1'),
			'panelLayout'             => 'filter;sort,search,limit',
		)
		// Zeilendarstellung und Operationen stehen weiter unten, weil sie sich
		// zwischen Contao 4.13 und Contao 5 unterscheiden
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{image_legend},singleSRC,imagedate;{info_legend},title,year,copyright;{active_legend},active'
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'foreignKey'              => 'tl_spielerregister.alias',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'singleSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('mandatory'=>true, 'filesOnly'=>true, 'fieldType'=>'radio', 'tl_class'=>'w50'),
			'sql'                     => "binary(16) NULL"
		),
		'imagedate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['imagedate'],
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 12,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50', 'maxlength'=>8),
			'sql'                     => "varchar(8) NOT NULL default ''"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'long clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'year' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['year'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>16, 'tl_class'=>'w50'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'copyright' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['copyright'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'active' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['active'],
			'toggle'                  => true, // Aktiviert den Contao-eigenen Schnellschalter in der Übersicht
			'default'                 => 1,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
	)
);

/*
 * Zeilendarstellung und Operationen versionsabhängig setzen (siehe
 * tl_spielerregister.php). Die Zeile der Elternansicht baut in beiden Fällen
 * tl_spielerregister_images::listImages() zusammen, angesprochen wird sie aber
 * unterschiedlich: Contao 4.13 kennt nur den child_record_callback, Contao 5.7
 * verwarnt diesen und erwartet stattdessen einen label_callback.
 */
if (ContaoSpielerregisterBundle::isContao5())
{
	$GLOBALS['TL_DCA']['tl_spielerregister_images']['list']['label']['label_callback'] = array('tl_spielerregister_images', 'listImagesLabel');

	$GLOBALS['TL_DCA']['tl_spielerregister_images']['list']['global_operations'] = array
	(
		'all'
	);

	$GLOBALS['TL_DCA']['tl_spielerregister_images']['list']['operations'] = array
	(
		'edit',
		'copy',
		'cut',
		'delete',
		'toggle',
		'show'
	);
}
else
{
	$GLOBALS['TL_DCA']['tl_spielerregister_images']['list']['sorting']['child_record_callback'] = array('tl_spielerregister_images', 'listImages');

	$GLOBALS['TL_DCA']['tl_spielerregister_images']['list']['global_operations'] = array
	(
		'all' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
			'href'                => 'act=select',
			'class'               => 'header_edit_all',
			'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
		)
	);

	$GLOBALS['TL_DCA']['tl_spielerregister_images']['list']['operations'] = array
	(
		'edit' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['edit'],
			'href'                => 'act=edit',
			'icon'                => 'edit.svg'
		),
		'copy' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['copy'],
			'href'                => 'act=paste&amp;mode=copy',
			'icon'                => 'copy.svg'
		),
		'cut' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['cut'],
			'href'                => 'act=paste&amp;mode=cut',
			'icon'                => 'cut.svg'
		),
		'delete' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['delete'],
			'href'                => 'act=delete',
			'icon'                => 'delete.svg',
			'attributes'          => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"'
		),
		'toggle' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['toggle'],
			'href'                => 'act=toggle&amp;field=active',
			'icon'                => 'visible.svg'
		),
		'show' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_spielerregister_images']['show'],
			'href'                => 'act=show',
			'icon'                => 'show.svg'
		)
	);
}

/**
 * Rückruffunktionen der Tabelle tl_spielerregister_images.
 */
class tl_spielerregister_images extends Backend
{
	/**
	 * Einstiegspunkt der Zeilendarstellung unter Contao 5.
	 *
	 * Contao 5 ruft das label_callback mit vier Parametern auf; gebraucht wird
	 * davon nur der Datensatz. Das ausgegebene HTML wird von Contao ungefiltert
	 * übernommen, das Vorschaubild bleibt also erhalten.
	 *
	 * @param array       $arrRow   Der Datensatz aus tl_spielerregister_images
	 * @param string      $strLabel Die von Contao vorbereitete Beschriftung
	 * @param object|null $dc       Der aufrufende Data Container
	 * @param array       $args     Die Werte der in "label.fields" genannten Spalten
	 *
	 * @return string Das HTML der Zeile
	 */
	public function listImagesLabel($arrRow, $strLabel = '', $dc = null, $args = array()): string
	{
		return $this->listImages($arrRow);
	}

	/**
	 * Erzeugt die Zeilendarstellung eines Fotos in der Übersicht.
	 *
	 * Neben dem Vorschaubild werden Dateiname, Jahr, Bildrechte und Titel
	 * angezeigt, damit sich die Fotos ohne Öffnen unterscheiden lassen.
	 *
	 * @param array $arrRow Der Datensatz aus tl_spielerregister_images
	 *
	 * @return string Das HTML der Zeile; fehlt die Datei, wird nur der Hinweis
	 *                auf die fehlende Datei ausgegeben
	 */
	public function listImages($arrRow)
	{
		$objFile = FilesModel::findByPk($arrRow['singleSRC']);

		if (null === $objFile)
		{
			return '<div class="tl_content_left">Datei nicht gefunden</div>';
		}

		$thumbnail = Bilder::vorschaubild((string) $objFile->path, 70, 70);

		$temp = '<div class="tl_content_left" style="min-width:300px">';

		if ('' !== $thumbnail)
		{
			$temp .= '<img src="'.$thumbnail.'" width="70" height="70" style="float:left; margin-right:5px" alt="">';
		}

		$temp .= 'Datei: <b>'.$objFile->path.'</b><br>';

		if ($arrRow['year'])
		{
			$temp .= 'Datum: <b>'.$arrRow['year'].'</b><br>';
		}

		if ($arrRow['copyright'])
		{
			$temp .= 'Rechte: <b>'.$arrRow['copyright'].'</b><br>';
		}

		if ($arrRow['title'])
		{
			$temp .= 'Titel: <b>'.$arrRow['title'].'</b><br>';
		}

		return $temp.'</div>';
	}
}
