# DSB-Spielerregister Changelog

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
