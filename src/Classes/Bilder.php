<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Classes;

use Contao\FilesModel;
use Contao\System;

/**
 * Erzeugt Vorschaubilder.
 *
 * Ersatz für die in Contao 5 entfallene Methode Image::get(), die im
 * Spielerregister an mehreren Stellen für kleine Vorschaubilder benutzt wurde.
 */
class Bilder
{
	/**
	 * Erzeugt ein Vorschaubild und liefert dessen URL.
	 *
	 * Die Bilddatei wird bei Bedarf erzeugt und im Cache-Verzeichnis von Contao
	 * abgelegt; existiert sie schon, wird sie wiederverwendet. Fehlt die Datei
	 * auf der Festplatte, kommt ein leerer String zurück – die aufrufende
	 * Stelle soll dann einfach kein Bild ausgeben statt einen toten Link.
	 *
	 * @param string $strPath  Pfad der Bilddatei, relativ zum Projektverzeichnis
	 * @param int    $intWidth Breite des Vorschaubildes in Pixeln
	 * @param int    $intHeight Höhe des Vorschaubildes in Pixeln
	 * @param string $strMode  Skalierungsmodus, etwa "crop", "proportional" oder
	 *                         "center_center"
	 *
	 * @return string URL des Vorschaubildes, oder ein leerer String, wenn sich
	 *                kein Bild erzeugen ließ
	 */
	public static function vorschaubild(string $strPath, int $intWidth, int $intHeight, string $strMode = 'crop'): string
	{
		if ('' === $strPath)
		{
			return '';
		}

		$figure = System::getContainer()
			->get('contao.image.studio')
			->createFigureBuilder()
			->fromPath($strPath)
			->setSize(array($intWidth, $intHeight, $strMode))
			->buildIfResourceExists();

		if (null === $figure)
		{
			return '';
		}

		return $figure->getImage()->getImageSrc();
	}

	/**
	 * Erzeugt ein Vorschaubild zu einer Datei-UUID aus der Dateiverwaltung.
	 *
	 * Bequeme Abkürzung für die Stellen, an denen nur die UUID vorliegt, etwa
	 * bei den Fotos in tl_spielerregister_images.
	 *
	 * @param mixed  $varUuid   Binäre oder als Zeichenkette notierte UUID
	 * @param int    $intWidth  Breite des Vorschaubildes in Pixeln
	 * @param int    $intHeight Höhe des Vorschaubildes in Pixeln
	 * @param string $strMode   Skalierungsmodus, etwa "crop" oder "proportional"
	 *
	 * @return string URL des Vorschaubildes, oder ein leerer String, wenn die
	 *                UUID zu keiner vorhandenen Datei gehört
	 */
	public static function vorschaubildAusUuid($varUuid, int $intWidth, int $intHeight, string $strMode = 'crop'): string
	{
		if (!$varUuid)
		{
			return '';
		}

		$objFile = FilesModel::findByUuid($varUuid);

		if (null === $objFile || !$objFile->path)
		{
			return '';
		}

		return self::vorschaubild((string) $objFile->path, $intWidth, $intHeight, $strMode);
	}
}
