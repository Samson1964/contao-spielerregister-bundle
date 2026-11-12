<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Hilfe zur HTML-Vorlage der Visitenkarten (Fragezeichen am Feld "HTML-Vorlage"
 * in tl_spielerregister_cards). Jede Zeile ist array(linke Spalte, rechte
 * Spalte); 'headspan' ergibt eine Zwischenüberschrift, 'colspan' einen Absatz
 * über die ganze Breite. Geschweifte Doppelklammern stehen als &#123;&#123; …
 * &#125;&#125; da, damit sie in keiner Contao-Version als Insert-Tag ersetzt
 * werden — angezeigt wird trotzdem {{ … }}.
 */
$GLOBALS['TL_LANG']['XPL']['spielerregister_visitenkarte'] = array
(
	array('colspan', 'Eine Visitenkarte ist normales HTML mit Platzhaltern. Bei der Ausgabe geschieht dreierlei, in dieser Reihenfolge:<br>1. <b>Platzhalter</b> wie <code>##name##</code> werden durch die Daten der Person ersetzt.<br>2. <b>Abfragen</b> wie <code>{if fide_id}…{endif}</code> blenden Teile ein oder aus.<br>3. <b>Insert-Tags</b> wie <code>&#123;&#123;dwz::##nuliga_id##&#125;&#125;</code> werden ausgeführt — mit den schon eingesetzten Werten.<br>Ist eine Abfrage unvollständig (etwa ein <code>{if}</code> ohne <code>{endif}</code>), lässt sich die Vorlage nicht speichern. Ein unbekannter Platzhalter bleibt dagegen sichtbar stehen — so fallen Tippfehler in der Vorschau auf.'),

	array('headspan', 'Zusammengesetzte Platzhalter'),
	array('<code>##name##</code>', 'Vor- und Nachname, z. B. „Erika Mustermann"'),
	array('<code>##name_umgekehrt##</code>', 'Nachname, Vorname'),
	array('<code>##lebensdaten##</code>', 'Geburts- und Sterbedaten samt Orten in einem Text, wie in den Listen des Registers'),
	array('<code>##geburtstag_text##</code>', 'Geburtstag als TT.MM.JJJJ (bei unvollständigem Datum MM.JJJJ oder JJJJ)'),
	array('<code>##todestag_text##</code>', 'Todestag wie oben; leer, solange die Person lebt'),
	array('<code>##alter##</code>', 'Heutiges Alter, bei Verstorbenen das Alter beim Tod. Leer, wenn das Geburts- oder Sterbedatum unvollständig ist'),
	array('<code>##fide_titel##</code>', 'Höchster FIDE-Titel: GM, IM, WGM oder WIM; leer ohne Titel'),
	array('<code>##fide_url##</code>', 'Adresse des FIDE-Profils (aus der FIDE-ID)'),
	array('<code>##wikipedia_url##</code>', 'Adresse des Artikels in der deutschen Wikipedia'),
	array('<code>##youtube_url##</code><br><code>##x_url##</code><br><code>##facebook_url##</code><br><code>##instagram_url##</code>', 'Fertige Profiladressen aus den eingetragenen Benutzernamen; leer, wenn kein Name eingetragen ist'),
	array('<code>##detail_link##</code>', 'Adresse der Detailseite dieser Person (Einstellung „Detailseite" des Spielerregisters)'),
	array('<code>##bild##</code>', 'Das Foto als fertige &lt;figure&gt; — mit der Bildgröße, Ausrichtung und Großansicht dieser Vorlage. Herkunft: das Referenzbild für Visitenkarten, sonst das jüngste Foto der Person'),
	array('<code>##bild_url##</code>', 'Nur die Adresse dieses Fotos, z. B. für <code>&lt;img src="##bild_url##" alt="##name##"&gt;</code> oder ein eigenes style-Attribut'),

	array('headspan', 'Felder des Registers'),
	array('colspan', 'Jedes Feld einer Person steht unter seinem Spaltennamen bereit. Datumsfelder liefern die gespeicherte Form JJJJMMTT (z. B. 19850122) — für die Anzeige also besser die zusammengesetzten Platzhalter oben nehmen. Ankreuzfelder liefern 1 oder nichts und eignen sich vor allem für Abfragen.'),
	array('<code>##firstname1##</code> <code>##surname1##</code>', 'Vorname, Nachname'),
	array('<code>##firstname2##</code> <code>##surname2##</code>', 'Alternativer Vor- und Nachname (weitere: …3, …4)'),
	array('<code>##title##</code>', 'Titel wie Dr. oder Prof.'),
	array('<code>##birthday##</code> <code>##birthplace##</code>', 'Geburtstag (JJJJMMTT), Geburtsort'),
	array('<code>##deathday##</code> <code>##deathplace##</code>', 'Todestag (JJJJMMTT), Todesort'),
	array('<code>##birthday_alt##</code> <code>##deathday_alt##</code>', 'Abweichende Geburts- und Todesdaten (JJJJMMTT)'),
	array('<code>##death##</code>', 'Verstorben (1 oder leer)'),
	array('<code>##shortinfo##</code> <code>##longinfo##</code>', 'Kurz- und Langbeschreibung als HTML; Insert-Tags darin sind schon ausgeführt'),
	array('<code>##importance##</code>', 'Zeitgeschichtliche Bedeutung, 1 bis 10'),
	array('<code>##fide_id##</code>', 'FIDE-ID'),
	array('<code>##nuliga_id##</code>', 'nuLiga-Personen-ID (NU1234567) — für die Insert-Tags des Wertungsportals'),
	array('<code>##wikipedia##</code>', 'Name des Wikipedia-Artikels'),
	array('<code>##homepage##</code>', 'Homepage der Person'),
	array('<code>##chessgames_id##</code> <code>##chess365_id##</code> <code>##chess_id##</code>', 'IDs bei chessgames.com, 365chess.com und chess.com'),
	array('<code>##social_youtube##</code> <code>##social_x##</code><br><code>##social_facebook##</code> <code>##social_instagram##</code>', 'Die nackten Benutzernamen'),
	array('<code>##gm_title##</code> <code>##im_title##</code><br><code>##wgm_title##</code> <code>##wim_title##</code>', 'FIDE-Titel vorhanden (1 oder leer)'),
	array('<code>##gm_date##</code> <code>##im_date##</code><br><code>##wgm_date##</code> <code>##wim_date##</code>', 'Verleihungsdatum des Titels (JJJJMMTT)'),
	array('<code>##honorpresident##</code> <code>##honormember##</code> …', 'Jahr der DSB-Ehrung: honorpresident, honormember, honorgoldpin, honorsilverpin, honorgoldbadge, honorsilverbadge, honorletter, honorplate, honormedal'),
	array('<code>##id##</code> <code>##alias##</code>', 'Datensatznummer und Alias der Person'),
	array('colspan', 'Nicht verfügbar sind die Fotoauswahl, das Referenzbild und die Weblinks (dafür gibt es <code>##bild##</code> und <code>##bild_url##</code>). Ist bei einer Person „Lebensdaten ausblenden" gesetzt, bleiben alle Geburts- und Sterbeangaben leer, auch <code>##alter##</code> und <code>##lebensdaten##</code>.'),

	array('headspan', 'Abfragen'),
	array('<code>{if fide_id}…{endif}</code>', 'Nur ausgeben, wenn das Feld nicht leer ist'),
	array('<code>{if not death}…{endif}</code>', 'Nur ausgeben, wenn das Feld leer ist'),
	array('<code>{if …}…{else}…{endif}</code>', 'Entweder – oder'),
	array('<code>{if …}…{elseif …}…{else}…{endif}</code>', 'Mehrere Fälle nacheinander; der erste zutreffende gewinnt'),
	array('<code>{if importance &gt;= 8}</code>', 'Vergleiche: <code>==</code> <code>!=</code> <code>&lt;</code> <code>&gt;</code> <code>&lt;=</code> <code>&gt;=</code>. Zahlen werden als Zahlen verglichen'),
	array('<code>{if fide_titel == "GM"}</code>', 'Text in Anführungszeichen'),
	array('<code>{if gm_title or im_title}</code>', 'Verknüpfen mit <code>and</code>, <code>or</code> und <code>not</code>; Klammern sind erlaubt'),
	array('<code>{if fide_titel in ["GM", "IM"]}</code>', 'Einer von mehreren Werten'),
	array('colspan', 'In Abfragen steht der Feldname <b>ohne</b> ##. Abfragen dürfen ineinander geschachtelt werden. Jede Abfrage braucht ihr eigenes <code>{endif}</code>.'),

	array('headspan', 'Insert-Tags'),
	array('colspan', 'Alle Insert-Tags von Contao und von installierten Erweiterungen sind erlaubt. Platzhalter dürfen darin stehen, weil sie vorher ersetzt werden.'),
	array('<code>&#123;&#123;dwz::##nuliga_id##&#125;&#125;</code>', 'Aktuelle DWZ (Wertungsportal-Bundle)'),
	array('<code>&#123;&#123;elo::##nuliga_id##&#125;&#125;</code>', 'Aktuelle Elo (Wertungsportal-Bundle)'),
	array('<code>&#123;&#123;ftitel::##nuliga_id##&#125;&#125;</code>', 'FIDE-Titel laut Wertungsportal; mit <code>::lang</code> ausgeschrieben'),
	array('<code>&#123;&#123;verein::##nuliga_id##&#125;&#125;</code>', 'Verein laut Wertungsportal; mit <code>::30</code> auf 30 Zeichen gekürzt'),
	array('<code>&#123;&#123;link_url::123&#125;&#125;</code>', 'Adresse der Seite 123 (Contao)'),
	array('<code>&#123;&#123;date::Y&#125;&#125;</code>', 'Aktuelles Jahr (Contao)'),
	array('colspan', 'Wird ein Wert gebraucht, der leer sein kann, gehört das Insert-Tag in eine Abfrage: <code>{if nuliga_id}DWZ &#123;&#123;dwz::##nuliga_id##&#125;&#125;{endif}</code>'),

	array('headspan', 'Bild und Gestaltung'),
	array('colspan', 'Größe, Ausrichtung und Großansicht des Fotos stellen Sie unten in dieser Vorlage ein; sie wirken auf <code>##bild##</code>. Das Hintergrundbild und die CSS-Klasse landen am äußeren Rahmen <code>&lt;div class="visitenkarte …"&gt;</code> und lassen sich im Stylesheet des Layouts weiter gestalten.'),

	array('headspan', 'Beispiel'),
	array('colspan', '<pre style="white-space:pre-wrap">'
		."&lt;div class=\"spielerbox\"&gt;\n"
		."  &lt;div class=\"spielerfoto\"&gt;##bild##&lt;/div&gt;\n"
		."  &lt;div class=\"spielertext\"&gt;\n"
		."    &lt;h3&gt;##name##&lt;/h3&gt;\n"
		."    {if fide_titel}&lt;p&gt;Titel: ##fide_titel##&lt;/p&gt;{endif}\n"
		."    {if nuliga_id}\n"
		."      &lt;p&gt;DWZ: &#123;&#123;dwz::##nuliga_id##&#125;&#125;&lt;/p&gt;\n"
		."      &lt;p&gt;Elo: &lt;a href=\"##fide_url##\"&gt;&#123;&#123;elo::##nuliga_id##&#125;&#125;&lt;/a&gt;&lt;/p&gt;\n"
		."    {endif}\n"
		."    {if alter}&lt;p&gt;Alter: ##alter## Jahre&lt;/p&gt;{endif}\n"
		."    {if wikipedia_url}&lt;p&gt;&lt;a href=\"##wikipedia_url##\"&gt;Wikipedia&lt;/a&gt;&lt;/p&gt;{endif}\n"
		."  &lt;/div&gt;\n"
		."&lt;/div&gt;"
		.'</pre>'),
);
