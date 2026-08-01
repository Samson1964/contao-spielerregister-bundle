<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle;

use Composer\InstalledVersions;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Haupt-Bundle-Klasse des Spielerregisters.
 */
class ContaoSpielerregisterBundle extends Bundle
{
	/**
	 * Prüft anhand der installierten Version, ob Contao 5 (oder neuer) läuft.
	 *
	 * Die DCA-Dateien brauchen diese Information, weil Contao 5 die
	 * Kurzschreibweise der Operationen kennt, bei der Beschriftung und Symbol
	 * aus dem Kern stammen, während Contao 4.13 vollständige Arrays benötigt.
	 *
	 * Ausgewertet wird der Composer-Laufzeitindex und nicht die Konstante
	 * VERSION, da diese in Contao 5 entfallen ist.
	 *
	 * @return bool True bei Contao 5 oder neuer, false bei Contao 4.13 und
	 *              ebenso dann, wenn sich die Version nicht ermitteln lässt
	 */
	public static function isContao5(): bool
	{
		if (!class_exists(InstalledVersions::class))
		{
			return false;
		}

		return version_compare(
			(string) InstalledVersions::getVersion('contao/core-bundle'),
			'5.0.0',
			'>='
		);
	}
}
