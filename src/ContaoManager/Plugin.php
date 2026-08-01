<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsletterBundle\ContaoNewsletterBundle;
use Schachbulle\ContaoSpielerregisterBundle\ContaoSpielerregisterBundle;

/**
 * Meldet die Erweiterung beim Contao Manager an.
 */
class Plugin implements BundlePluginInterface
{
	/**
	 * Nennt das Bundle und seine Ladereihenfolge.
	 *
	 * Das Spielerregister wird nach dem Kern und nach dem Newsletter-Bundle
	 * geladen: Es ergänzt tl_newsletter_recipients um die Versandzeit des
	 * Jahrestags-Newsletters, was nur funktioniert, wenn die Grundfassung
	 * dieser DCA-Datei bereits steht.
	 *
	 * @param ParserInterface $parser Vom Manager gestellter Parser; wird hier
	 *                                nicht gebraucht, gehört aber zur Schnittstelle
	 *
	 * @return array Liste mit der Bundle-Beschreibung
	 */
	public function getBundles(ParserInterface $parser): array
	{
		return [
			BundleConfig::create(ContaoSpielerregisterBundle::class)
				->setLoadAfter([ContaoCoreBundle::class, ContaoNewsletterBundle::class]),
		];
	}
}
