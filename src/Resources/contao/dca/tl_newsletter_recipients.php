<?php

// Datenbankabfrage, wenn Tabelle tl_newsletter_recipients aktiv ist und eine ID übergeben wurde
// Leider wird das trotzdem noch im Info-Popup angezeigt
if(\Input::get('table') == 'tl_newsletter_recipients' && \Input::get('id'))
{
	// ID des Newsletter-Verteilers laden
	$objNewsletter = \Database::getInstance()->prepare("SELECT pid FROM tl_newsletter_recipients WHERE id = ?")
	                                         ->execute(\Input::get('id'));

	if($objNewsletter->numRows)
	{
		if($objNewsletter->pid == $GLOBALS['TL_CONFIG']['spielerregister_newsletter'])
		{
			// Palette manipulieren
			$GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default'] = str_replace('active', 'active,spielerregister_mailTime', $GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default']);
		}
	}
}

/**
 * Neue Felder in tl_newsletter_recipients
 */

// Zeitstempel des letzten Versands des Spielerregisters
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
