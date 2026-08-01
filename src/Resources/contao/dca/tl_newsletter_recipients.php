<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Config;
use Contao\Database;
use Contao\Input;

/*
 * Die Versandzeit wird nur bei dem Verteiler eingeblendet, der in den
 * Einstellungen für das Spielerregister ausgewählt ist. Bei allen anderen
 * Verteilern hat das Feld keine Bedeutung.
 *
 * Hinweis: Im Info-Fenster ("Datensatzdetails") wird das Feld trotzdem
 * angezeigt, weil dort die komplette Tabelle ausgegeben wird.
 */
if (Input::get('table') == 'tl_newsletter_recipients' && Input::get('id'))
{
	// Verteiler des Empfängers laden
	$objNewsletter = Database::getInstance()
		->prepare("SELECT pid FROM tl_newsletter_recipients WHERE id = ?")
		->execute(Input::get('id'));

	if ($objNewsletter->numRows && $objNewsletter->pid == Config::get('spielerregister_newsletter'))
	{
		// Palette erweitern
		$GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default'] = str_replace(
			'active',
			'active,spielerregister_mailTime',
			$GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default']
		);
	}
}

/*
 * Neue Felder in tl_newsletter_recipients
 */

// Zeitpunkt des letzten Versands des Jahrestags-Newsletters
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['spielerregister_mailTime'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_newsletter_recipients']['spielerregister_mailTime'],
	'exclude'                 => true,
	'flag'                    => 5,
	'inputType'               => 'text',
	'eval'                    => array
	(
		'rgxp'                => 'datim',
		'datepicker'          => true,
		'tl_class'            => 'w50 wizard'
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);
