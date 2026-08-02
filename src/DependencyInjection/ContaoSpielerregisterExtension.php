<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Lädt die Dienste des Bundles in den Symfony-Container.
 */
class ContaoSpielerregisterExtension extends Extension
{
	/**
	 * Liest die services.yaml des Bundles ein.
	 *
	 * @param array<string, mixed> $mergedConfig Aufbereitete Konfiguration; das Bundle
	 *                                       besitzt keine eigene Konfiguration und
	 *                                       wertet den Wert deshalb nicht aus
	 * @param ContainerBuilder $container    Container, in den die Dienste kommen
	 *
	 * @return void
	 */
	public function load(array $mergedConfig, ContainerBuilder $container): void
	{
		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__.'/../Resources/config')
		);

		$loader->load('services.yaml');
	}
}
