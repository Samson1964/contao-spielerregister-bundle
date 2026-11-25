<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt das Spielerregister des Deutschen Schachbundes
 * für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoSpielerregisterBundle\Picker;

use Contao\CoreBundle\Picker\AbstractInsertTagPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Eintrag "Spielerregister" im Link-Auswahlfenster des Backends.
 *
 * Neben Seiten, Artikeln, Nachrichten usw. erscheint im Auswahlfenster für
 * Links (Linkfelder, Link-Dialog des Editors) ein Reiter "Spielerregister".
 * Dort wird eine Person ausgewählt; eingesetzt wird der Insert-Tag
 * {{spielerregister_url::ID}}, der im Frontend zur Detailseite der Person
 * aufgelöst wird.
 *
 * Eine Klasse für Contao 4.13 und 5: Die Schnittstellen unterscheiden sich in
 * Parametertypen (4.13 ohne, 5 mit Typangaben) und in getDcaTable(), das in
 * Contao 5 eine PickerConfig als optionalen Parameter erhält. Untypisierte bzw.
 * optionale Parameter sind mit beiden Fassungen verträglich, weil PHP in
 * abgeleiteten Klassen breitere Parametertypen und zusätzliche optionale
 * Parameter erlaubt. Der Sicherheitsdienst wird ohne Typangabe übergeben, weil
 * seine Klasse zwischen den Symfony-Versionen den Namensraum gewechselt hat.
 */
class SpielerregisterPickerProvider extends AbstractInsertTagPickerProvider implements DcaPickerProviderInterface
{
	/**
	 * @var object Dienst security.helper (Symfony\Component\Security\Core\Security
	 *             in Contao 4.13, Symfony\Bundle\SecurityBundle\Security in Contao 5)
	 */
	private $security;

	/**
	 * Übernimmt die Dienste, die der Kern für Picker-Anbieter vorsieht.
	 *
	 * @param FactoryInterface         $menuFactory Erzeugt den Reiter im Auswahlfenster
	 * @param RouterInterface          $router      Baut die Adresse der Personenliste im Auswahlfenster
	 * @param TranslatorInterface|null $translator  Übersetzt die Reiterbeschriftung MSC.spielerregisterPicker
	 * @param object                   $security    Dienst security.helper für die Rechteprüfung
	 */
	public function __construct(FactoryInterface $menuFactory, RouterInterface $router, ?TranslatorInterface $translator, $security)
	{
		parent::__construct($menuFactory, $router, $translator);

		$this->security = $security;
	}

	/**
	 * Liefert den internen Namen des Anbieters.
	 *
	 * Er dient zugleich als Schlüssel der Reiterbeschriftung
	 * ($GLOBALS['TL_LANG']['MSC']['spielerregisterPicker']) und als CSS-Klasse
	 * des Reiters.
	 *
	 * @return string Immer "spielerregisterPicker"
	 */
	public function getName(): string
	{
		return 'spielerregisterPicker';
	}

	/**
	 * Prüft, ob der Reiter im aktuellen Auswahlfenster angeboten wird.
	 *
	 * Nur beim Auswählen von Links, und nur für Benutzer, die das
	 * Backend-Modul Spielerregister benutzen dürfen — sonst führte der Reiter
	 * auf eine Liste, die der Benutzer gar nicht öffnen darf.
	 *
	 * @param mixed $context Art des Auswahlfensters, etwa "link", "file" oder "page"
	 *
	 * @return bool true, wenn der Reiter angezeigt werden soll
	 */
	public function supportsContext($context): bool
	{
		return 'link' === $context && $this->security->isGranted('contao_user.modules', 'spielerregister');
	}

	/**
	 * Prüft, ob der vorhandene Feldinhalt von diesem Anbieter stammt.
	 *
	 * Steht im Linkfeld schon {{spielerregister_url::…}}, öffnet das
	 * Auswahlfenster direkt diesen Reiter und markiert die Person.
	 *
	 * @param PickerConfig $config Konfiguration des Auswahlfensters samt aktuellem Wert
	 *
	 * @return bool true, wenn der Wert ein Spielerregister-Insert-Tag ist
	 */
	public function supportsValue(PickerConfig $config): bool
	{
		return $this->isMatchingInsertTag($config);
	}

	/**
	 * Liefert die Tabelle, aus der ausgewählt wird.
	 *
	 * @param PickerConfig|null $config Wird nur von Contao 5 übergeben und hier nicht gebraucht
	 *
	 * @return string Immer "tl_spielerregister"
	 */
	public function getDcaTable($config = null): string
	{
		return 'tl_spielerregister';
	}

	/**
	 * Liefert die Einstellungen für die Auswahlliste.
	 *
	 * Es wird genau eine Person gewählt (Optionsfeld). Ist bereits ein
	 * Insert-Tag eingetragen, wird dessen ID vorausgewählt; Zusatzangaben wie
	 * |urlattr bleiben als Flags erhalten.
	 *
	 * @param PickerConfig $config Konfiguration des Auswahlfensters
	 *
	 * @return array<string, mixed> Attribute für DC_Table im Auswahlmodus
	 */
	public function getDcaAttributes(PickerConfig $config): array
	{
		$attributes = array('fieldType' => 'radio');

		if ($this->supportsValue($config))
		{
			$attributes['value'] = $this->getInsertTagValue($config);

			if ($flags = $this->getInsertTagFlags($config))
			{
				$attributes['flags'] = $flags;
			}
		}

		return $attributes;
	}

	/**
	 * Wandelt die gewählte Person in den einzusetzenden Insert-Tag um.
	 *
	 * @param PickerConfig $config Konfiguration des Auswahlfensters
	 * @param mixed        $value  ID der gewählten Person
	 *
	 * @return string Zum Beispiel "{{spielerregister_url::815}}"
	 */
	public function convertDcaValue(PickerConfig $config, $value): string
	{
		return sprintf($this->getInsertTag($config), $value);
	}

	/**
	 * Liefert die Parameter der Backend-Adresse, die das Auswahlfenster lädt.
	 *
	 * @param PickerConfig|null $config Konfiguration, oder null für den Reiter selbst
	 *
	 * @return array<string, string> Immer do=spielerregister (die Personenliste)
	 */
	protected function getRouteParameters(?PickerConfig $config = null): array
	{
		return array('do' => 'spielerregister');
	}

	/**
	 * Liefert die Vorlage des Insert-Tags, in die die ID eingesetzt wird.
	 *
	 * @return string "{{spielerregister_url::%s}}"
	 */
	protected function getDefaultInsertTag(): string
	{
		return '{{spielerregister_url::%s}}';
	}
}
