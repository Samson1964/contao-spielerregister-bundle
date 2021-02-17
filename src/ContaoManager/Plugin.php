<?php

namespace Schachbulle\ContaoSpielerregisterBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Schachbulle\ContaoSpielerregisterBundle\ContaoSpielerregisterBundle;

class Plugin implements BundlePluginInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getBundles(ParserInterface $parser)
	{
		return [
			BundleConfig::create(ContaoSpielerregisterBundle::class)
				->setLoadAfter([ContaoCoreBundle::class, ContaoNewsletterBundle::class]),
		];
	}
}
