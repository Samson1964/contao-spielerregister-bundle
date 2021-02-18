# DSB-Spielerregister Changelog

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
