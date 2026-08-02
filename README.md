# DSB-Spielerregister

Erweiterung für **Contao 4.13 und Contao 5**, die eine Personendatenbank für den
Schachsport bereitstellt: Lebensdaten, Fotos, FIDE-Titel, Ehrungen des Deutschen
Schachbundes und Weblinks. Im Frontend stehen Detailseite, Jahrestagslisten,
Ehrungslisten, Sterbeliste und Titellisten zur Verfügung, dazu ein
Inhaltselement, drei Insert-Tags und zwei Cronjobs für den Newsletterversand.

## Inhalt

- [Voraussetzungen](#voraussetzungen)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Personen im Backend pflegen](#personen-im-backend-pflegen)
- [Frontend-Module](#frontend-module)
- [Inhaltselement](#inhaltselement)
- [Insert-Tags](#insert-tags)
- [Templates und Dateien](#templates-und-dateien)
- [Newsletter-Cronjobs](#newsletter-cronjobs)
- [Datenbanktabellen](#datenbanktabellen)
- [Umstieg von Version 2.x auf 3.0](#umstieg-von-version-2x-auf-30)
- [Entwicklung](#entwicklung)

## Voraussetzungen

| | |
|---|---|
| Contao | 4.13 LTS oder 5.x (geprüft mit 4.13.58 und 5.7.7) |
| PHP | 7.4 oder 8.x (geprüft mit 8.3) |
| Weitere Erweiterungen | `contao/newsletter-bundle`, `menatwork/contao-multicolumnwizard-bundle`, `schachbulle/contao-helper-bundle` |

Die Abhängigkeiten installiert Composer automatisch mit.

## Installation

Über den Contao Manager das Paket `schachbulle/contao-spielerregister-bundle`
suchen und installieren, oder auf der Kommandozeile:

```bash
composer require schachbulle/contao-spielerregister-bundle
```

Anschließend die Datenbank aktualisieren – im Contao Manager unter
„Wartung → Datenbank aktualisieren" oder auf der Kommandozeile:

```bash
vendor/bin/contao-console contao:migrate
```

Danach steht im Backend unter **Inhalte → Spielerregister** die Personenverwaltung
bereit.

## Einstellungen

Alle zentralen Einstellungen stehen unter **System → Einstellungen**. Die beiden
ersten Felder sollten direkt nach der Installation gesetzt werden, alles
Weitere braucht nur, wer die Newsletter nutzt.

### Abschnitt „Spielerregister"

| Einstellung | Bedeutung |
|---|---|
| **Detailseite** | Die Seite, auf der das Frontend-Modul „Spielerdetails" eingebunden ist. Alle Links auf eine Person werden aus dieser Seite gebildet. Ohne diese Angabe bleiben die Links in Ehrungs-, Sterbe- und Titelliste sowie in den Insert-Tags leer. |
| **Bildgröße** | Größe der Vorschaubilder in den Frontend-Modulen. Es lassen sich die in Contao angelegten Bildgrößen verwenden oder eigene Maße eintragen. |

### Abschnitt „Spielerregister: Newsletter"

| Einstellung | Bedeutung |
|---|---|
| **Newsletter-Verteiler** | Der Verteiler, an den die beiden Cronjobs ihre E-Mails schicken. |
| **Jahrestags-Newsletter automatisch versenden** | Schaltet den stündlichen Versand der anstehenden Geburts- und Todestage ein. Ohne diesen Haken passiert nichts. |
| **Newsletter über neue Einträge automatisch versenden** | Schaltet die tägliche Meldung neu aufgenommener Personen ein. |
| **Anzahl Mails** | Höchstzahl der E-Mails je Durchgang (Vorgabe 30). `0` verschickt alle E-Mails in einem Durchgang. |
| **Wartezeit** | Pause in Sekunden zwischen zwei Durchgängen. `0` bedeutet: ein Durchgang je Cron-Lauf. |
| **Absenderadresse** | Pflichtangabe für den Versand. Fehlt sie, verschicken die Cronjobs nichts und schreiben eine Meldung ins Protokoll. |
| **Absendername** | Name, der im E-Mail-Programm des Empfängers erscheint. |
| **Betreff** | Betreff des Jahrestags-Newsletters. |
| **Basis-URL** | Adresse der Website ohne abschließenden Schrägstrich, z. B. `https://www.schachbund.de`. Sie wird den Links in den E-Mails vorangestellt, damit sie im E-Mail-Programm funktionieren. |
| **Einleitung** | HTML-Text am Anfang des Jahrestags-Newsletters. |
| **Fußzeile** | HTML-Text am Ende der Newsletter. Der Platzhalter `##email##` wird durch die Adresse des jeweiligen Empfängers ersetzt und eignet sich für einen Abmeldelink. |

## Personen im Backend pflegen

Das Backend-Modul **Inhalte → Spielerregister** listet alle Personen mit
Nachname, Vorname, Geburts- und Sterbetag auf.

### Eingabefelder

| Gruppe | Felder |
|---|---|
| **Name** | Nachname, Vorname, Titel (z. B. „Dr."), Alias |
| **Alternative Namen** | Bis zu drei weitere Nach- und Vornamen, etwa Geburtsnamen oder abweichende Schreibweisen. Die Suche im Frontend berücksichtigt alle vier Nachnamen. |
| **Lebensdaten** | Geburtstag, Geburtsort, abweichender Geburtstag, Kennzeichen „verstorben" (blendet Todestag, Todesort und abweichenden Todestag ein), „Lebensdaten ausblenden" |
| **Fotos** | Dateiauswahl aus der Dateiverwaltung (mehrere Dateien oder ein Ordner) |
| **Informationen** | Kurzinfo, Weblinks zum Beitrag, Langinfo |
| **Weblinks** | Wikipedia-Stichwort, FIDE-ID, DeWIS-ID, chessgames.com, 365chess.com, chess.com, Homepage |
| **Bedeutung** | Zeitgeschichtliche Bedeutung von 1 (gering) bis 10 (hoch) |
| **FIDE-Titel** | GM, IM, WGM, WIM mit jeweiligem Verleihungsdatum |
| **Ehrungen des DSB** | Ehrenpräsident, Ehrenmitglied, Goldene/Silberne Ehrennadel, Goldene/Silberne Ehrenplakette, Ehrenbrief, Ehrenteller, Bundesmedaille – jeweils mit Jahreszahl |
| **Intern** | Interne Notizen, die nicht im Frontend erscheinen |
| **Aktiv** | „Keine Hervorhebung" (nimmt die Person aus dem Modul „Aktueller Jahrestag" heraus) und „Aktiv" |

### Datumsangaben

Datumsangaben werden als `TT.MM.JJJJ`, `MM.JJJJ` oder `JJJJ` eingegeben und in
der Datenbank als Zahl `JJJJMMTT` abgelegt; unbekannte Bestandteile stehen dort
als Nullen. Aus „1868" wird also `18680000` und in der Ausgabe wieder „1868".
Das ist Absicht: In einem historischen Register ist oft nur das Jahr bekannt.

Wo aus unvollständigen Angaben gerechnet werden muss, gilt:

* **Sterbealter** (Sterbeliste): nur bei vollständigem Geburts- und Sterbedatum,
  sonst steht dort ein Fragezeichen.
* **Verleihungsalter** (Titelliste): fehlt der Monat, wird der Juni angenommen,
  fehlt der Tag, der Monatserste. Der Wert ist dann näherungsweise.

### Alias

Das Alias wird beim Speichern automatisch aus allen Vor- und Nachnamen gebildet
und ist umlautfrei (aus „Müller-Schäfer, Jörg" wird `mueller-schaefer-joerg`).
Eine eigene Eingabe wird beim nächsten Speichern überschrieben.

### Bedeutung (1 bis 10)

Die Bedeutung steuert, ab welcher Stufe eine Person in den Jahrestagslisten
erscheint. Die Stufe 10 ist dabei besonders: Personen mit Bedeutung 10 werden in
den Jahrestagsmodulen **immer** angezeigt, unabhängig von der eingestellten
Mindeststufe.

### Fotos

Für Fotos gibt es zwei Wege:

1. **Dateiauswahl am Datensatz** (Feld „Quelldateien"). Ausgewählt werden können
   einzelne Dateien oder ein Ordner; bei einem Ordner werden dessen Dateien eine
   Ebene tief eingelesen. Bildunterschrift, Titel und Alternativtext stammen aus
   den Metadaten der Dateiverwaltung.
2. **Fototabelle** (Operation „Spielerfotos bearbeiten" in der Übersicht). Jedes
   Foto ist ein eigener Datensatz mit Aufnahmedatum, Titel, Jahresangabe als
   Text, Bildrechten und Schalter „aktiv".

Sind am Datensatz Dateien ausgewählt, haben diese Vorrang; nur wenn dort nichts
hinterlegt ist, wird die Fototabelle gelesen. Bildrechte in eckigen Klammern –
etwa `Siegerehrung [Foto: Meier]` – werden in der Ausgabe als eigener Block
`<div class="rechte">` vor die Bildunterschrift gestellt.

### Infobox

Über dem Eingabeformular steht eine Infobox mit vorbereiteten Suchlinks zu
Google, 365chess.com, chessgames.com und chess.com sowie dem Zugang zu den Fotos
der Person. Vorhandene Fotos werden dort als kleine Vorschaubilder angezeigt.

### Erweiterter Filter

Über der Liste steht ein zusätzliches Auswahlfeld, das Lücken im Datenbestand
aufdeckt. Die Auswahl bleibt in der Sitzung erhalten, bis sie zurückgesetzt wird.

| Filter | Zeigt Personen … |
|---|---|
| Runde Geburtstage | … mit rundem Geburtstag im laufenden Jahr (30, 40, 50, 60, 65, 70, 75, 80, 85, 90, 95, 100), die nicht als verstorben gekennzeichnet sind |
| Älter als 100 Jahre | … die vor über 100 Jahren geboren und nicht als verstorben gekennzeichnet sind |
| Geburtsdatum fehlt | … ohne Geburtsdatum |
| Sterbedatum fehlt | … die als verstorben gekennzeichnet sind, aber kein Sterbedatum haben |
| Geburtsdatum unvollständig | … deren Geburtsdatum keinen Tag enthält |
| Sterbedatum unvollständig | … deren Sterbedatum keinen Tag enthält |
| Geburtsort fehlt | … ohne Geburtsort |
| Sterbeort fehlt | … die als verstorben gekennzeichnet sind, aber keinen Sterbeort haben |
| Kurzinfo fehlt | … ohne Kurzinfo |
| Vorname fehlt | … ohne Vornamen |

### CSV-Export

Die Schaltfläche **CSV-Export** über der Liste lädt alle Personen als
CSV-Datei herunter (Trennzeichen Semikolon, Spalten: Name, Vorname,
Geburtsdatum, Sterbedatum, Wertung). Die Datumsangaben stehen in der Datei in
der Datenbankform `JJJJMMTT`.

## Frontend-Module

Alle Module stehen im Backend unter **Layout → Module** in der Gruppe
„Spielerregister" zur Verfügung.

### Spielerdetails (`spielerregister_playerdetail`)

Zeigt eine einzelne Person mit Lebensdaten, Fotos, Kurz- und Langinfo sowie den
Links zu Wikipedia, chessgames.com und 365chess.com. Zusätzlich enthält das
Modul ein Suchformular.

* **Einstellung:** Weiterleitungsseite (die Seite mit diesem Modul, für die
  Links in der Trefferliste)
* **Aufruf:** Die Person wird über das Auto-Item `/player/<ID>` ausgewählt, also
  zum Beispiel `https://example.org/personen/player/815.html`. Diese Links
  erzeugen die anderen Module und Insert-Tags automatisch.
* **Suche:** Das Formular schickt den Suchbegriff als Parameter `psearch` an
  dieselbe Seite. Gesucht wird in allen vier Nachnamenfeldern, der Suchbegriff
  muss mindestens zwei Zeichen lang sein.
* **Angezeigt werden** nur aktive Personen.
* **Seitentitel:** Der Name der Person wird dem Seitentitel vorangestellt.
* **Template:** `spielerregister_playerdetail`

### Jahrestagliste (`spielerregister_yeardaylist`)

Listet alle Personen auf, deren Geburts- oder Todestag in den nächsten Tagen
ansteht – nach Datum sortiert, mit Foto, Kurzinfo und Weblinks.

* **Einstellungen:** Weiterleitungsseite, Spieleranzeige ab Level,
  „Jahrestage heute plus x Tage"
* **Besonderheit:** Der Zeitraum darf über den Jahreswechsel hinausgehen.
* **Template:** `spielerregister_yeardays`

### Aktueller Jahrestag (`spielerregister_yearday`)

Zeigt den nächsten anstehenden Jahrestag als Einzelmeldung, gedacht für eine Box
in der Seitenspalte. Betrachtet werden die kommenden 60 Tage.

* **Einstellungen:** Weiterleitungsseite (Ziel des Links „Mehr Jahrestage"),
  Spieleranzeige ab Level
* **Besonderheit:** Personen mit „Keine Hervorhebung" bleiben außen vor. Das
  Foto stammt aus der Fototabelle (jüngste Aufnahme, 120 × 120 Pixel).
* **Template:** `spielerregister_yearday`

### Ehrungslisten (`spielerregister_honorlist`)

Gibt eine der neun Ehrungen des Deutschen Schachbundes als Liste aus, absteigend
nach dem Jahr der Verleihung.

* **Einstellung:** Listenart – Ehrenpräsidenten, Ehrenmitglieder, Goldene bzw.
  Silberne Ehrennadel, Goldene bzw. Silberne Ehrenplakette, Ehrenbrief,
  Ehrenteller, Bundesmedaille
* **Template:** `spielerregister_honorlist`

### Sterbeliste (`spielerregister_deathlist`)

Listet die zuletzt verstorbenen Personen auf, die jüngsten zuerst, mit
Sterbedatum, Sterbealter und Kurzinfo.

* **Einstellung:** Anzahl Monate, die zurückgeschaut wird
* **Template:** `spielerregister_deathlist`

### FIDE-Titelliste (`spielerregister_titlelist`)

Listet alle Träger eines FIDE-Titels auf, sortiert nach dem Alter bei der
Verleihung – die jüngsten Titelträger stehen oben.

* **Einstellung:** Listenart – Großmeister, Internationale Meister,
  Großmeisterinnen, Internationale Meisterinnen
* **Template:** `spielerregister_titlelist`

## Inhaltselement

Das Inhaltselement **Person aus dem Spielerregister** (Gruppe „Include-Elemente")
gibt eine Person mit Lebensdaten, Kurzinfo, Weblinks und Langinfo aus, ohne dass
die Daten im Artikel doppelt gepflegt werden müssen.

* **Einstellung:** Person auswählen (Auswahlliste aller Personen mit
  Lebensdaten zur Unterscheidung von Namensgleichen)
* **Überschrift:** Enthält die Überschrift den Platzhalter `%s`, wird dort der
  Name der Person eingesetzt – aus „Zum 100. Geburtstag von %s" wird also
  „Zum 100. Geburtstag von Emanuel Lasker".
* **Template:** `ce_spielerregister_person`

## Insert-Tags

### `{{spielerregister_url::ID}}`

    {{spielerregister_url::815}}

Gibt die Adresse der Detailansicht für die Person mit der ID 815 zurück,
gebildet aus der in den Einstellungen gewählten Detailseite. Ohne eingestellte
Detailseite bleibt die Ausgabe leer.

### `{{spielerregister_box::ID}}`

    {{spielerregister_box::815}}

Gibt den Namen der Person als anklickbaren Text aus. Beim Klick öffnet sich eine
Dialogbox mit Name, Lebensdaten und Kurzinfo.

    {{spielerregister_box::815::Klicken}}

Wie oben, aber mit „Klicken" als Linktext.

> **Hinweis:** Die Dialogbox nutzt jQuery UI. Das dafür nötige JavaScript liegt
> im Bundle und wird vom Template eingebunden; jQuery selbst muss im
> Seitenlayout aktiviert sein.

### `{{register::…}}`

    {{register::815}}
    {{register::815|Der zweite Weltmeister}}
    {{register::Lasker, Emanuel}}
    {{register::Emanuel Lasker}}

Gibt den Namen der Person als Link aus, der eine Lightbox mit Lebensdaten,
Fotos, Wikipedia-Link und Kurzinfo öffnet. Angegeben werden kann die
Spielerregister-ID oder der Name in der Schreibweise „Nachname, Vorname" bzw.
„Vorname Nachname"; hinter dem senkrechten Strich steht wahlweise ein eigener
Anzeigetext.

> **Hinweis:** Bei der Suche über den Namen muss genau eine Person gefunden
> werden. Gibt es mehrere Personen gleichen Namens, wird nur der Text ausgegeben
> – in diesem Fall die ID verwenden.

## Templates und Dateien

Alle Templates lassen sich wie gewohnt überschreiben, indem sie in das
Verzeichnis `templates/` der Contao-Installation kopiert werden.

| Template | Verwendung |
|---|---|
| `spielerregister_playerdetail` | Modul „Spielerdetails" |
| `spielerregister_yeardays` | Modul „Jahrestagliste" |
| `spielerregister_yearday` | Modul „Aktueller Jahrestag" |
| `spielerregister_honorlist` | Modul „Ehrungslisten" |
| `spielerregister_deathlist` | Modul „Sterbeliste" |
| `spielerregister_titlelist` | Modul „FIDE-Titelliste" |
| `ce_spielerregister_person` | Inhaltselement „Person aus dem Spielerregister" |
| `spielerregister_infobox` | Lightbox des Insert-Tags `{{register::…}}` |
| `spielerregister_modalbox` | Dialogbox des Insert-Tags `{{spielerregister_box::…}}` |
| `spielerregister_person` | Alternative Darstellung einer Person; wird von keinem Modul benutzt und steht für eigene Anpassungen bereit |
| `spielerregister_suche` | Eigenständiges Suchformular; wird von keinem Modul benutzt |
| `be_spielerregister` | Platzhalter der Module in der Backend-Seitenstruktur |

Bei mehreren Fotos binden die Module ein einfaches Bilderkarussell ein
(`bundles/contaospielerregister/js/js-image-slider.js` und die zugehörige
CSS-Datei). Wer eine eigene Darstellung möchte, überschreibt das Template und
lässt die Dateien einfach weg.

## Newsletter-Cronjobs

Das Bundle bringt zwei Cronjobs mit. Beide laufen über den Contao-Cron, es ist
also kein eigener Aufruf per URL mehr nötig – Voraussetzung ist, dass der
Contao-Cron läuft (entweder als „Poor-Man-Cron" über Seitenaufrufe oder per
`vendor/bin/contao-console contao:cron`).

| Cronjob | Intervall | Aufgabe |
|---|---|---|
| Jahrestags-Newsletter | stündlich | Verschickt die anstehenden Geburts- und Todestage: heute, morgen, in einer Woche und in zwei Wochen |
| Neue Einträge | täglich | Meldet die in den letzten 24 Stunden neu aufgenommenen Personen |

Beide verschicken erst dann E-Mails, wenn der jeweilige Haken in den
Einstellungen gesetzt und eine Absenderadresse hinterlegt ist.

**Jahrestags-Newsletter im Einzelnen:** Je Durchgang werden höchstens so viele
E-Mails verschickt, wie unter „Anzahl Mails" eingestellt ist. Wer heute schon
eine E-Mail bekommen hat, wird übersprungen – jeder Empfänger erhält also
höchstens eine E-Mail pro Tag, unabhängig davon, wie oft der Cron läuft. Der
Zeitpunkt des Versands steht am Empfänger in `tl_newsletter_recipients` und wird
im Backend beim eingestellten Verteiler angezeigt. Ist eine Wartezeit
eingestellt, wiederholt der Cronjob den Durchgang, bis alle Empfänger versorgt
sind.

Jede E-Mail wird einzeln verschickt, damit der Platzhalter `##email##` in der
Fußzeile durch die Adresse des Empfängers ersetzt werden kann. Insert-Tags im
Text werden vor dem Versand aufgelöst.

## Datenbanktabellen

| Tabelle | Inhalt |
|---|---|
| `tl_spielerregister` | Die Personen mit allen Angaben |
| `tl_spielerregister_images` | Fotos einer Person mit Datum, Titel und Bildrechten |

Zusätzlich ergänzt das Bundle vorhandene Tabellen: `tl_content` um die
Personenauswahl des Inhaltselements, `tl_module` um die Moduleinstellungen,
`tl_settings` um die oben beschriebenen Einstellungen und
`tl_newsletter_recipients` um die Versandzeit des Jahrestags-Newsletters.

## Umstieg von Version 2.x auf 3.0

Version 3.0 ist unter Contao 4.13 und Contao 5 lauffähig. Beim Aktualisieren
einer bestehenden Installation ist Folgendes zu beachten:

1. **Aufrufskripte entfallen.** `public/jahrestage.php` und
   `public/aenderungen.php` gibt es nicht mehr; sie liefen über
   `system/initialize.php`, das Contao 5 nicht mehr kennt. Ein externer Cron, der
   bisher `…/bundles/contaospielerregister/jahrestage.php` aufgerufen hat, muss
   abgeschaltet werden. Stattdessen den Haken „Jahrestags-Newsletter automatisch
   versenden" setzen und sicherstellen, dass der Contao-Cron läuft.
2. **Absender und Rahmentexte pflegen.** Absenderadresse, Absendername, Betreff,
   Basis-URL, Einleitung und Fußzeile standen früher fest im Quelltext und
   stehen jetzt in den Einstellungen. Ohne Absenderadresse verschickt der
   Cronjob nichts.
3. **Detailseite eintragen.** Ehrungs-, Sterbe- und Titelliste haben früher fest
   auf `person/player/<ID>.html` verlinkt. Jetzt wird die unter
   System → Einstellungen gewählte Detailseite verwendet.
4. **Datenbank aktualisieren.** Die neuen Einstellungsfelder brauchen einen
   Datenbankabgleich.
5. **Die Moduleinstellung „Bildanzeige ab Level" ist entfallen.** Sie war ohne
   Wirkung.
6. **codefog/contao-haste wird nicht mehr benötigt.** Der Schnellschalter in der
   Übersicht läuft jetzt über die Contao-eigene Umschaltung.

## Entwicklung

Die Klassen, die ohne Contao auskommen – im Wesentlichen die Datumsberechnungen
in `Classes/Datum.php` und die Hilfsfunktionen in `Klassen/Helper.php` – sind mit
Unit-Tests abgedeckt:

```bash
vendor/bin/phpunit
```

Die Testsuite lädt die Klassen bei Bedarf über einen eigenen Autoloader in
`tests/bootstrap.php` und läuft deshalb auch ohne installiertes
`vendor`-Verzeichnis mit einem eigenständigen PHPUnit 9.

Der übrige Code lässt sich nur im laufenden Contao prüfen. Dafür wird die
Erweiterung in je eine Testinstallation von Contao 4.13 und Contao 5 eingebunden
und dort werden alle Frontend-Module (mit jeder Einstellungsvariante), das
Inhaltselement, die Insert-Tags, die Backend-Listen samt Bearbeitungsmaske, der
erweiterte Filter, der CSV-Export und die beiden Cronjobs aufgerufen. Ein
Fehler-Handler, der nur Meldungen aus dem Verzeichnis der Erweiterung
durchlässt, macht Warnungen und Verfallsmeldungen dabei sichtbar; alles andere
wäre im Grundrauschen von Contao und Symfony nicht zu finden.

**Frank Hoppe**
