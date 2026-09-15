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
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['title_legend'] = 'Visitenkarte';
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['html_legend'] = 'Vorlage';
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['image_legend'] = 'Foto';
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['layout_legend'] = 'Gestaltung';

/*
 * Felder
 */
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['title'] = array('Bezeichnung', 'Name der Visitenkarte, unter dem sie im Frontend-Modul ausgewählt wird.');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['html'] = array('HTML-Vorlage', 'Platzhalter: ##spaltenname## für jedes Feld des Registers, dazu ##name##, ##alter##, ##lebensdaten##, ##fide_titel##, ##bild##, ##detail_link##, ##wikipedia_url##, ##fide_url##, ##youtube_url##, ##x_url##, ##facebook_url##, ##instagram_url## u. a. Abfragen: {if nuliga_id}…{else}…{endif}. Insert-Tags wirken nach den Platzhaltern, z. B. {{dwz::##nuliga_id##}}. Vollständige Liste im README.');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['size'] = array('Bildgröße', 'Größe des Fotos, das über ##bild## eingefügt wird.');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['floating'] = array('Bildausrichtung', 'Ausrichtung des Fotos (CSS-Klasse float_above, float_left, float_right oder float_below).');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['fullsize'] = array('Großansicht', 'Das Foto beim Anklicken in der Lightbox vergrößern.');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['backgroundSRC'] = array('Hintergrundbild', 'Wird als background-image an den Rahmen der Visitenkarte (div.visitenkarte) gesetzt. Größe und Wiederholung über das Stylesheet des Layouts regeln.');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['cssClass'] = array('CSS-Klasse', 'Zusätzliche Klasse für den Rahmen der Visitenkarte, etwa "spielerbox".');

/*
 * Operationen
 */
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['new'] = array('Neue Visitenkarte', 'Eine neue Visitenkarte anlegen');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['edit'] = array('Visitenkarte bearbeiten', 'Visitenkarte ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['copy'] = array('Visitenkarte kopieren', 'Visitenkarte ID %s kopieren');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['delete'] = array('Visitenkarte löschen', 'Visitenkarte ID %s löschen');
$GLOBALS['TL_LANG']['tl_spielerregister_cards']['show'] = array('Details anzeigen', 'Details der Visitenkarte ID %s anzeigen');
