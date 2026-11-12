# DSB-Spielerregister Changelog

## Version 3.3.1 (2026-11-12)

* Fix: Veröffentlicht die Änderungen von 3.3.0 erneut. Der Tag 3.3.0 war
  versehentlich auf den Stand von 3.2.0 gesetzt worden; Packagist liefert unter
  3.3.0 deshalb dauerhaft den Code von 3.2.0 aus (ein Umsetzen des Tags lässt
  Packagist nicht zu). Inhaltlich identisch mit dem Eintrag zu 3.3.0 unten —
  bitte 3.3.1 oder neuer verwenden.

## Version 3.3.0 (2026-11-12)

* Add: **Hilfe zur HTML-Vorlage der Visitenkarten.** Das Fragezeichen am Feld
  „HTML-Vorlage" öffnet den Contao-Hilfeassistenten mit allen Platzhaltern
  (zusammengesetzte und Felder des Registers), Abfragen mit Vergleichen und
  Verknüpfungen, Insert-Tags samt Wertungsportal-Beispielen, Hinweisen zu Bild
  und Hintergrund sowie einer Beispielvorlage (`languages/de/explain.php`).
* Change: **Sterbeliste/Gedenktafel zeigt auf Wunsch alle Verstorbenen.**
  „Anzahl Monate" = 0 hebt die Zeitgrenze auf; Personen ohne bekanntes
  Sterbedatum stehen dann am Ende mit „unbekannt". Neu in den Einstellungen
  ist „Elemente pro Seite" (Kernfeld `perPage`) für eine Seitennavigation;
  gezählt und geblättert wird in der Datenbank. Unter Contao 5.7 über den
  Dienst `contao.pagination.factory`, unter 4.13 über `Contao\Pagination`.
  Eine Seitenzahl außerhalb der Liste ergibt die 404-Seite.
* Change: „Anzahl Monate" nimmt nur noch Zahlen an (vorher `alnum`).

## Version 3.2.0 (2026-09-15)

* Add: **Visitenkarten.** Neue Schaltfläche „Visitenkarten" über der
  Personenliste führt zu beliebig vielen Vorlagen (Tabelle
  `tl_spielerregister_cards`). Eine Vorlage ist HTML mit Platzhaltern
  (`##name##`, `##alter##`, jede Spalte des Registers …) und Abfragen
  (`{if nuliga_id}…{else}…{endif}`), ausgewertet mit dem Simple-Token-Parser
  des Contao-Kerns. Danach werden Insert-Tags ersetzt, auch fremde wie
  `{{dwz::##nuliga_id##}}` aus dem Wertungsportal-Bundle. Je Vorlage
  einstellbar: Bildgröße, Bildausrichtung, Großansicht, Hintergrundbild und
  CSS-Klasse. Eine fehlerhafte Abfrage wird schon beim Speichern gemeldet.
* Add: Frontend-Modul **„Visitenkarte"**: Person und Vorlage auswählen.
* Add: Feld **„Referenzbild für Visitenkarten"** (`cardSRC`, kein Pflichtfeld).
  Ohne Referenzbild nimmt die Visitenkarte das jüngste Foto aus der
  Dateiauswahl, sonst aus der Fototabelle.
* Add: Felder für **YouTube, X, Facebook und Instagram** (`social_youtube`,
  `social_x`, `social_facebook`, `social_instagram`). Gespeichert wird nur der
  Benutzername; eine eingefügte Profiladresse oder ein führendes „@" wird beim
  Speichern und beim CSV-Import entfernt. Die Adresse entsteht erst bei der
  Ausgabe (`Helper::sozialerLink()`), auch auf der Detailseite.
* Change: **DeWIS-ID durch „nuLiga-Personen-ID" (`nuliga_id`) ersetzt.** Die
  Spalte `dewis_id` war im Datenbestand von schachbund.de bei allen 3061
  Personen leer; die Datenbankaktualisierung schlägt ihr Löschen vor. Der
  CSV-Import erkennt statt `dewis_id` jetzt `nuliga_id` und die vier
  Social-Media-Spalten.
* Change: Alias-Erzeugung und CSV-Wertumwandlung liegen in `Klassen/Helper.php`;
  dazu 13 neue Unit-Tests (jetzt 41).

## Version 3.1.2 (2026-08-24)

Nachprüfung ohne funktionale Änderung.

* Change: Mit echtem **PHP 8.4.24** gegen Contao 4.13.58 und 5.7.7 geprüft
  (Syntax, 28 Unit-Tests, alle Frontend-Module, drei Insert-Tags,
  DCA-Rückruffunktionen, CSV-Import inklusive Formular, CSV-Export, beide
  Cronjobs, Datenbankabgleich). Kein einziger Hinweis aus dem Bundle selbst;
  `composer.json` verlangte mit `php: ^7.4 || ^8.0` bereits 8.4, das war also
  nur zu bestätigen, nicht zu ändern.
* Change: Alle 66 Textdateien des Repos auf gültiges UTF-8 und auf ein BOM
  geprüft (Vorgabe: UTF-8 ohne BOM). Keine Datei betroffen — die zuvor in
  Windows-1252 gespeicherte `TODO.md` (unversioniert, siehe „TODO.md nicht
  versionieren") war bereits beim Verschieben des vorigen Punktes behoben
  worden.

## Version 3.1.1 (2026-08-24)

* Fix: Das Formular des CSV-Imports war unformatiert – Trennzeichen und
  Quelldatei hingen am linken Rand. Ursache war rohes, selbst
  zusammengesetztes HTML ohne die Contao-eigenen Klassen
  `fieldset.tl_tbox`/`div.widget.w50`/`div.widget.clr`, von denen das
  Backend-Stylesheet die Einrückung und den Zeilenumbruch der Felder ableitet.
  Ersetzt durch eine echte Vorlage
  (`Resources/contao/templates/be_spielerregister_import.html5`), deren
  Feldstruktur wörtlich dem Kern-Template `be_csv_import.html5` entspricht
  (identisch in Contao 4.13 und 5). Im Browser gegen die Referenzseite
  `tl_newsletter_recipients` (`contao/newsletter-bundle`) verglichen: Position
  und Fließverhalten der Felder stimmen jetzt exakt überein.

## Version 3.1.0 (2026-08-24)

* Add: **CSV-Import** über die neue Schaltfläche in der Übersicht (key=import,
  `Classes/Import.php`). Erkannt werden alle Felder außer den Fotos und den
  Weblinks (Spalte `links`), da beide als verschachtelte Daten in einer
  flachen CSV-Zeile nicht sinnvoll abzubilden sind. Existiert bereits eine
  Person mit demselben Vor- und Nachnamen, wird die Zeile übersprungen; am
  Ende steht je Datei eine Meldung mit der Anzahl importierter Personen sowie
  den übersprungenen bzw. fehlgeschlagenen Zeilen. Details siehe README,
  Abschnitt „CSV-Import".
* Change: Die Erzeugung des Alias (`Helper::erzeugeAlias()`) steht jetzt an
  einer Stelle und wird sowohl beim manuellen Speichern im Backend als auch
  vom CSV-Import benutzt, der ohne den Data Container direkt in die Datenbank
  schreibt.

## Version 3.0.2 (2026-08-04)

* Fix: **Eine unbrauchbare Adresse im Newsletter-Verteiler ließ beide Cronjobs
  abbrechen** — unbemerkt, weil dabei niemand zusieht. `Contao\Email` prüft die
  Adressen und wirft eine Ausnahme; der Versand stand aber außerhalb einer
  Fehlerbehandlung.
  * Beim **Jahrestags-Newsletter** ging je Empfänger eine eigene Nachricht
    hinaus. Ein Fehler kostet jetzt nur noch diese eine Mail; alle folgenden
    Empfänger bekommen ihre wie gewohnt.
  * Beim **Newsletter über neue Einträge** stehen alle Empfänger in einer
    einzigen Blindkopie. Unbrauchbare Adressen werden deshalb vorher
    aussortiert, damit sie nicht allen anderen die Nachricht verhageln.
* Change: Übergangene Adressen erscheinen mit Klartext im Systemprotokoll,
  damit der Fehler im Verteiler auffindbar ist

## Version 3.0.1 (2026-08-02)

Geprüft mit PHP 8.3 gegen Contao 4.13.58 und Contao 5.7.7, jeweils mit dem
Datenbestand von schachbund.de (3061 Personen).

* Fix: Die Einstellungsseite des Backends brach unter Contao 5 mit „You have
  requested a non-existent service `contao.image.image_sizes`“ ab. Die
  Auswahlliste der Bildgröße holt den Dienst jetzt unter seinem aktuellen Namen
  `contao.image.sizes`, den auch Contao 4.13 kennt.
* Fix: Die Fotoliste einer Person ließ sich unter Contao 5 gar nicht öffnen
  („ValueError: The arguments array must contain 1 items, 0 given“). Contao 5
  setzt die Zeilenbeschriftung aus den unter `list.label.fields` genannten
  Spalten zusammen, bevor es das `label_callback` aufruft; ohne diese Angabe
  läuft der Kern in `vsprintf('%s', array())`.
* Fix: Auf der Detailseite einer Person blieben die ausführlichen Informationen
  immer leer – gelesen wurde die Spalte `langinfo`, die es nicht gibt; sie heißt
  `longinfo`.
* Change: Frontend-URLs entstehen jetzt an einer Stelle (`Helper::seitenUrl()`)
  über den Dienst `contao.routing.content_url_generator`, sofern vorhanden;
  `PageModel::getFrontendUrl()` ist seit Contao 5.3 veraltet und entfällt in
  Contao 6. Unter Contao 4.13 bleibt es beim bisherigen Weg.
* Change: Das Suchformular der Detailseite bekommt seine Adresse aus dem
  Symfony-Request statt aus `Environment::get('indexFreeRequest')`; letzteres
  löste unter Contao 5 zwei Verfallsmeldungen aus.
* Change: Der Sitzungsbehälter des erweiterten Filters wird ausdrücklich auf
  `AttributeBagInterface` geprüft, dazu vollständige Array-Typangaben in elf
  Kommentarblöcken (PHPStan Stufe 6 ohne Beanstandung).

## Version 3.0.0 (2026-08-01)

Die Erweiterung läuft jetzt unter Contao 4.13 **und** Contao 5 sowie unter PHP 8.

* Add: Unterstützung für Contao 5 und PHP 8; `composer.json` verlangt `contao/core-bundle ^4.13 || ^5.0` und `php ^7.4 || ^8.0`
* Add: Zwei Cronjobs (`Cron/Jahrestage.php` stündlich, `Cron/Aenderungen.php` täglich) ersetzen die Aufrufskripte
* Add: Einstellungen für den Newsletterversand (Automatikschalter, Absender, Absendername, Betreff, Basis-URL, Einleitung, Fußzeile); Absender und Rahmentexte standen bisher fest im Quelltext
* Add: `Classes/Datum.php` bündelt die Datumsberechnungen, die vorher in fünf Klassen nahezu gleich standen; dazu 23 Unit-Tests in `tests/`
* Add: `Classes/SpielerregisterModul.php` als gemeinsame Basis der Frontend-Module
* Add: `Classes/Bilder.php` erzeugt Vorschaubilder über das Contao-Bildstudio
* Add: Ausführliche `README.md` mit Beschreibung aller Module, Einstellungen und Insert-Tags
* Delete: `public/jahrestage.php` und `public/aenderungen.php` – sie liefen über `system/initialize.php`, das es in Contao 5 nicht mehr gibt. Ein externer Cron auf diese Adressen muss abgeschaltet werden!
* Delete: Abhängigkeit `codefog/contao-haste`; der Schnellschalter läuft jetzt über das Contao-eigene `act=toggle`
* Delete: Moduleinstellung „Bildanzeige ab Level" (`spielerregister_image`) – sie wurde von keinem Modul ausgewertet
* Change: Alle Klassen mit `declare(strict_types=1)`, Namensraum-Importen und deutschen Kommentarblöcken; die in Contao 5 entfallenen globalen Klassenaliase werden nicht mehr benutzt
* Change: `TL_MODE`, `Session::getInstance()`, `Image::get()`, `Controller::addImageToTemplate()`, `Controller::generateFrontendUrl()`, `Controller::replaceInsertTags()`, `array_insert()`, `specialchars()`, `ampersand()`, `REQUEST_TOKEN`, `VERSION`/`BUILD` und `contao/main.php` durch die aktuellen Schnittstellen ersetzt
* Change: Ehrungs-, Sterbe- und Titelliste verlinken die Person über die eingestellte Detailseite statt über das fest eingebaute `person/player/<ID>.html`
* Change: Modul „Aktueller Jahrestag" verlinkt „Mehr Jahrestage" auf die eingestellte Weiterleitungsseite statt auf `persoenlichkeiten.html`
* Change: CSV-Export liefert eine echte Download-Antwort statt einer Ausgabe mitten in der Backend-Seite
* Change: `dataContainer` als Klassennamen (`DC_Table::class`), `validImageTypes` über den Container-Parameter
* Fix: `Helper::putDate()` zerlegte jede zehn Zeichen lange Eingabe als Datum – aus „irgendwann" wurde der Wert „wannenir"
* Fix: Insert-Tag `{{register::…}}` füllte die Lightbox nur mit dem Namen; Lebensdaten, Kurzinfo, Wikipedia-Link und Fotos fehlten
* Fix: Beim Einlesen eines Bilderordners wurde die erste Datei übersprungen
* Fix: Modul „Aktueller Jahrestag" und die Ehrungsliste brachen mit einem Fehler ab, sobald ein Foto geladen werden sollte (unqualifizierter Aufruf von `Image::get()`)
* Fix: Ehrungs- und Titelliste erzeugten ohne ausgewählte Listenart einen SQL-Fehler
* Fix: Templates verwiesen auf Bilder unter `system/modules/spielerregister/…`, die es seit Contao 4 nicht mehr gibt

## Version 2.6.3 (2026-07-30)

* Fix: Der Insert-Tag `{{register::…}}` fand einen Spieler praktisch nie: die ID-Suche prüfte mit `is_int()` auf einen Wert, den `explode()` immer als String liefert, und die dokumentierte Schreibweise "Nachname, Vorname" wurde gar nicht ausgewertet
* Fix: Das Template `spielerregister_infobox` las die Bilddaten unter den Schlüsseln `singleSRC`, `thumb`, `id` und `caption`, geliefert werden aber `image`, `thumbnail`, `imageID` und `imageCaption` -> leere Links und Bilder im Lightbox-Inhalt
* Fix: `ReplacePlayer()` belegt jetzt alle vom Template gelesenen Variablen vor, das vermeidet Warnungen wegen undefinierter Werte (`$objPlayer`, `$data[1]`, `slider`, `images`)
* Change: Bei gefundenem Spieler wird die Templatevariable `gefunden` auf true gesetzt

## Version 2.6.2 (2026-07-29)

* Fix: Warning: Undefined array key "deleteConfirm" bei contao:migrate -> Lesezugriffe auf $GLOBALS['TL_LANG'] in den DCA-Dateien mit `?? null` bzw. `?? array()` abgesichert, da der DcaLoader die Sprachdateien noch nicht geladen hat

## Version 2.6.1 (2024-06-25)

* Fix: PHP Warning: Invalid argument supplied for foreach() in Module/YeardayList.php on line 115
* Fix: Warning: foreach() argument must be of type array|object, bool given in ContentElements/Person.php (line 40)

## Version 2.6.0 (2024-05-05)

* Add: Abhängigkeit menatwork/contao-multicolumnwizard-bundle
* Fix: Warning: Undefined array key "editalias" in dca/tl_spielerregister.php (line 935) 
* Fix: Warning: Undefined array key "tl_registerFilter" in dca/tl_spielerregister.php (line 1018) 
* Add: tl_spielerregister.links (mit MCW) um die Links zu einem Beitrag aufzunehmen
* Add: Filter tl_spielerregister.wikipedia und tl_spielerregister.homepage

## Version 2.5.1 (2024-04-18)

* Add: codefog/contao-haste
* Change: Haste-Toggler statt des normalen Togglers

## Version 2.5.0 (2023-06-18)

* Add: PHP 8 in composer.json als erlaubt eingetragen

## Version 2.4.2 (2021-09-30)

* Fix: Falsche Variable beim Fix in Version 2.4.1

## Version 2.4.1 (2021-09-30)

* Fix: Ausgabe der Lebensdaten bei der Suche in der Trefferliste unerwünscht, wenn Anzeige Lebensdaten deaktiviert ist

## Version 2.4.0 (2021-09-30)

* Add: Option Lebensdaten im Frontend ausblenden
* Add: ContentElements/Person.php, um eine Person aus dem Spielerregister auszugeben

## Version 2.3.1 (2021-05-19)

* Add: TODO.md
* Add: Detailseite in tl_settings
* Change: Classes/Tags.php greift jetzt auf die Detailseite aus den Einstellungen zurück
* Add: JQuery-UI-Skript
* Add: Template spielerregister_modalbox für Ansicht der Spielerdetails in einer Modalbox/Lightbox

## Version 2.3.0 (2021-05-18)

* Add: Inserttag spielerregister_url

## Version 2.2.1 (2021-02-25)

* Delete: public/ehrungen.php (da schon länger ausgelagert in das contao-tributes-bundle)
* Change: public/jahrestage.php - Newsletter an Admin entfernt
* Add: public/jahrestage - Anzahl verschickter Newsletter in tl_log schreiben

## Version 2.2.0 (2021-02-19)

* Add: Maximale Anzahl der E-Mails die pro Durchgang von jahrestage.php verschickt werden -> System-Einstellungen
* Change: Auswahl des Newsletter-Verteilers ist jetzt alphabetisch sortiert
* Add: Feld spielerregister_mailTime nur anzeigen, wenn richtiger Verteiler angezeigt wird

## Version 2.1.1 (2021-02-18)

* Fix: UltraEdit speichert UTF8 mit BOM fälschlicherweise

## Version 2.1.0 (2021-02-18)

* Add: Max. 30 Newsletter werden versendet an Empfänger die am aktuellen Tag noch nichts bekommen haben
* Add: Newsletter-Verteiler-Auswahl in System-Einstellungen (statt Eingabe der ID)

## Version 2.0.2 (2021-02-17)

* Add: System-Einstellungen - Angabe der Newsletter-ID für Jahrestage-Newsletter
* Add: Feld spielerregister_mailTime in tl_newsletter_recipients für die Speicherung des Newsletterversands
* Add: Abhängigkeit contao/newsletter-bundle

## Version 2.0.1 (2020-12-17)

* Fix: Umlaute beim Alias wurden nicht ersetzt
* Fix: Links wurden in jahrestage.php nicht richtig ersetzt
* Fix: Backend Suchen-Filter verbessert
* Fix: Infobox bei Spieler bearbeiten zu weit links
* Change: Funktion listPersons durch listRecords ausgetauscht, dabei BE-Ansicht verbessert
* Change: Übersetzungen tl_spielerregister überarbeitet
* Fix: tl_spielerregister.importance als int statt char definiert, damit numerisch sortiert werden kann

## Version 2.0.0 (2020-12-14)

* Fix: Too few arguments to build the query string (dca/tl_spielerregister.php->execute - line 990) - SQL-Fehler bei "Nicht verstorben, älter als 100 Jahre"
* Add: Abhängigkeit schachbulle/contao-helper-bundle
* Add: Auskommentierte Aufrufe \Samson\Helper ersetzt durch \Schachbulle\ContaoHelperBundle\Classes\Helper
* Add: Bildgröße in System -> Einstellungen festlegen
* Fix: Funktion \Schachbulle\ContaoSpielerregisterBundle\Klassen\Spielerregister::Bilder umgebaut
* Change: YeardayList.php komplett neu programmiert
* Change: Template spielerregister_yeardays komplett auf Contao 4 umgebaut
* Change: PlayerDetail.php an die neue Bildgenerierung angepaßt
* Change: Template spielerregister_yeardays komplett auf Contao 4 umgebaut

## Version 1.0.4 (2020-10-28)

* Voreingestellte includeBlankOption bei Helper-Klasse getRegister entfernt

## Version 1.0.3 (2020-06-30)

* htaccess in public entfernt

## Version 1.0.2 (2020-06-26)

* Fix: jahrestage.php für Contao 4 angepaßt
* Entfernung von überflüssigen Dateien im Root
* Anpassungen an Contao 4
 
## Version 1.0.1 (2020-03-11)

* Add: Funktion getPlayerlink in Helper-Klasse eingebaut, vorerst hardcodiert

## Version 1.0.0 (2020-03-04)

* Delete: Prosearch-Sprachdateien entfernt
* Add: Helper-Funktion putDate
* Info: $caption = \Samson\Helper::replaceCopyright() überall vorerst auskommentiert
* Fix: Spielerexport

## Version 0.0.1 (2020-03-04)

* Ersteinrichtung der Erweiterung (Version 1.5.0) für Contao 4 als Bundle
