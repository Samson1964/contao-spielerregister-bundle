<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Autoloader für die Tests.
 *
 * Steht ein vendor-Verzeichnis zur Verfügung (composer install im Bundle), wird
 * dessen Autoloader benutzt. Ohne vendor-Verzeichnis – so läuft die Suite hier
 * mit dem eigenständigen PHPUnit aus F:\Claude\tools\phpunit9 – werden die
 * Klassen des Bundles selbst geladen. Die geprüften Klassen kommen bewusst ohne
 * Contao aus.
 */
if (is_file(__DIR__.'/../vendor/autoload.php'))
{
	require __DIR__.'/../vendor/autoload.php';

	return;
}

spl_autoload_register(
	static function (string $strClass): void
	{
		$strPrefix = 'Schachbulle\\ContaoSpielerregisterBundle\\';

		if (0 !== strncmp($strClass, $strPrefix, \strlen($strPrefix)))
		{
			return;
		}

		$strRest = substr($strClass, \strlen($strPrefix));

		// Testklassen liegen in tests/, alles andere in src/
		if (0 === strncmp($strRest, 'Tests\\', 6))
		{
			$strFile = __DIR__.'/'.str_replace('\\', '/', substr($strRest, 6)).'.php';
		}
		else
		{
			$strFile = __DIR__.'/../src/'.str_replace('\\', '/', $strRest).'.php';
		}

		if (is_file($strFile))
		{
			require $strFile;
		}
	}
);
