<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\BackendUser;
use Contao\Database;
use Contao\System;

/*
 * Palette
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{spielerregister_legend:hide},spielerregister_detailseite,spielerregister_imageSize;{spielerregister_mail_legend:hide},spielerregister_newsletter,spielerregister_cron,spielerregister_cron_neu,spielerregister_zyklus,spielerregister_wartezeit,spielerregister_mailAbsender,spielerregister_mailAbsendername,spielerregister_mailBetreff,spielerregister_mailUrl,spielerregister_mailKopf,spielerregister_mailFuss';

/*
 * Felder
 */

// Seite, auf der das Modul "Spielerdetails" eingebunden ist
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_detailseite'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_detailseite'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'foreignKey'              => 'tl_page.title',
	'eval'                    => array
	(
		'mandatory'           => true,
		'fieldType'           => 'radio',
		'tl_class'            => 'w50 clr'
	),
	'sql'                     => "int(10) unsigned NOT NULL default 0",
	'relation'                => array
	(
		'type'                => 'hasOne',
		'load'                => 'lazy'
	)
);

// Größe der Fotos in den Frontend-Modulen
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_imageSize'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_imageSize'],
	'exclude'                 => true,
	'inputType'               => 'imageSize',
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array
	(
		'rgxp'                => 'natural',
		'includeBlankOption'  => true,
		'nospace'             => true,
		'helpwizard'          => true,
		'tl_class'            => 'w50 clr'
	),
	// Liefert die im System hinterlegten Bildgrößen als Auswahlliste, beschränkt
	// auf die Größen, die der angemeldete Benutzer sehen darf. Der Dienst heißt
	// seit Contao 5 "contao.image.sizes"; unter Contao 4.13 ist
	// "contao.image.image_sizes" nur noch ein Alias darauf, der alte Name führt
	// in Contao 5 dagegen zu einem Fehler.
	'options_callback'        => static function ()
	{
		return System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance());
	},
	'sql'                     => "varchar(255) NOT NULL default ''"
);

// Newsletter-Verteiler, an den die Cronjobs ihre E-Mails schicken
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_newsletter'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_newsletter'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_settings_spielerregister', 'Newsletterverteiler'),
	'eval'                    => array
	(
		'includeBlankOption'  => true,
		'tl_class'            => 'w50'
	),
	'sql'                     => "int(10) NOT NULL default 0"
);

// Schalter für den Jahrestags-Newsletter
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_cron'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_cron'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50 m12'
	),
	'sql'                     => "char(1) NOT NULL default ''"
);

// Schalter für den Newsletter über neue Einträge
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_cron_neu'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_cron_neu'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50 m12'
	),
	'sql'                     => "char(1) NOT NULL default ''"
);

// Anzahl der E-Mails je Durchgang
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_zyklus'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_zyklus'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array
	(
		'maxlength'           => 3,
		'rgxp'                => 'digit',
		'tl_class'            => 'w50'
	),
	'sql'                     => "int(3) NOT NULL default '30'"
);

// Wartezeit zwischen zwei Durchgängen
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_wartezeit'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_wartezeit'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array
	(
		'maxlength'           => 2,
		'rgxp'                => 'digit',
		'tl_class'            => 'w50'
	),
	'sql'                     => "int(2) NOT NULL default '0'"
);

// Absenderadresse der Newsletter
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_mailAbsender'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailAbsender'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array
	(
		'rgxp'                => 'email',
		'maxlength'           => 255,
		'decodeEntities'      => true,
		'tl_class'            => 'w50 clr'
	),
	'sql'                     => "varchar(255) NOT NULL default ''"
);

// Anzeigename des Absenders
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_mailAbsendername'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailAbsendername'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array
	(
		'maxlength'           => 255,
		'decodeEntities'      => true,
		'tl_class'            => 'w50'
	),
	'sql'                     => "varchar(255) NOT NULL default ''"
);

// Betreff des Jahrestags-Newsletters
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_mailBetreff'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailBetreff'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array
	(
		'maxlength'           => 255,
		'decodeEntities'      => true,
		'tl_class'            => 'w50 clr'
	),
	'sql'                     => "varchar(255) NOT NULL default ''"
);

// Basis-URL für die Links in den E-Mails
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_mailUrl'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailUrl'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array
	(
		'rgxp'                => 'url',
		'maxlength'           => 255,
		'decodeEntities'      => true,
		'tl_class'            => 'w50'
	),
	'sql'                     => "varchar(255) NOT NULL default ''"
);

// Einleitung der E-Mail
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_mailKopf'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailKopf'],
	'exclude'                 => true,
	'inputType'               => 'textarea',
	'eval'                    => array
	(
		'allowHtml'           => true,
		'tl_class'            => 'clr'
	),
	'sql'                     => "text NULL"
);

// Fußzeile der E-Mail
$GLOBALS['TL_DCA']['tl_settings']['fields']['spielerregister_mailFuss'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailFuss'],
	'exclude'                 => true,
	'inputType'               => 'textarea',
	'eval'                    => array
	(
		'allowHtml'           => true,
		'tl_class'            => 'clr'
	),
	'sql'                     => "text NULL"
);

/**
 * Stellt die Auswahlliste der Newsletter-Verteiler bereit.
 */
class tl_settings_spielerregister
{
	/**
	 * Liest alle Newsletter-Verteiler für das Auswahlfeld.
	 *
	 * Die Liste ist alphabetisch nach dem Namen des Verteilers sortiert, weil
	 * die Reihenfolge der IDs für Redakteure nichts aussagt.
	 *
	 * @param object|null $dc Der aufrufende Data Container; wird nicht ausgewertet
	 *
	 * @return array<int, string> Verteiler-ID => Name des Verteilers; leer, wenn
	 *                            noch kein Verteiler angelegt wurde
	 */
	public function Newsletterverteiler($dc = null): array
	{
		$optionen = array();

		$objNewsletter = Database::getInstance()
			->prepare("SELECT id, title FROM tl_newsletter_channel ORDER BY title ASC")
			->execute();

		while ($objNewsletter->next())
		{
			$optionen[(int) $objNewsletter->id] = $objNewsletter->title;
		}

		return $optionen;
	}
}
