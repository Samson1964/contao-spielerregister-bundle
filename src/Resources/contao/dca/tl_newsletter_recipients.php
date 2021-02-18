<?php

// Palette manipulieren
$debug = print_r($GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default'], true);

log_message($debug,'spielerregister.log');
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default'] = str_replace('active', 'active,spielerregister_mailTime', $GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default']);

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
