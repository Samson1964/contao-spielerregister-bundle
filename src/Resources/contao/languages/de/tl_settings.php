<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Legenden
 */
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_legend'] = 'Spielerregister';
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mail_legend'] = 'Spielerregister: Newsletter';

/*
 * Felder
 */
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_detailseite'] = array('Detailseite', 'Seite, auf der das Modul "Spielerdetails" eingebunden ist. Sie wird für alle Links auf eine Person gebraucht.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_imageSize'] = array('Bildgröße', 'Größe der Vorschaubilder in den Frontend-Modulen.');

$GLOBALS['TL_LANG']['tl_settings']['spielerregister_newsletter'] = array('Newsletter-Verteiler', 'Verteiler, an den die beiden Cronjobs des Spielerregisters ihre E-Mails schicken.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_cron'] = array('Jahrestags-Newsletter automatisch versenden', 'Verschickt stündlich die anstehenden Geburts- und Todestage an den Verteiler. Jeder Empfänger bekommt höchstens eine E-Mail pro Tag.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_cron_neu'] = array('Newsletter über neue Einträge automatisch versenden', 'Meldet einmal täglich die in den letzten 24 Stunden neu aufgenommenen Personen an den Verteiler.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_zyklus'] = array('Anzahl Mails', 'Anzahl der E-Mails, die je Durchgang verschickt werden. 0 = kein Zyklus, alle E-Mails in einem Durchgang.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_wartezeit'] = array('Wartezeit', 'Wartezeit in Sekunden nach jedem Durchgang. 0 = keine Wartezeit, nur ein Durchgang je Cron-Lauf.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailAbsender'] = array('Absenderadresse', 'Absenderadresse der Newsletter. Ohne diese Angabe verschicken die Cronjobs nichts.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailAbsendername'] = array('Absendername', 'Name, der im E-Mail-Programm des Empfängers als Absender erscheint.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailBetreff'] = array('Betreff', 'Betreff des Jahrestags-Newsletters. Ohne Angabe wird "Jahrestage im Spielerregister" verwendet.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailUrl'] = array('Basis-URL', 'Adresse der Website ohne abschließenden Schrägstrich, zum Beispiel https://www.schachbund.de. Sie wird den Links in den E-Mails vorangestellt.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailKopf'] = array('Einleitung', 'HTML-Text am Anfang des Jahrestags-Newsletters. Ohne Angabe wird eine schlichte Überschrift verwendet.');
$GLOBALS['TL_LANG']['tl_settings']['spielerregister_mailFuss'] = array('Fußzeile', 'HTML-Text am Ende der Newsletter, etwa Hinweise und Abmeldelink. Der Platzhalter ##email## wird durch die Adresse des Empfängers ersetzt.');
