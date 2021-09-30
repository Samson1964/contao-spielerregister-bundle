<?php

namespace Schachbulle\ContaoSpielerregisterBundle\ContentElements;

class Person extends \ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_spielerregister_person';

	/**
	 * Generate the module
	 */
	protected function compile()
	{

		// Person aus Datenbank laden, wenn ID übergeben wurde
		if($this->spielerregister_id)
		{
			$objPerson = $this->Database->prepare("SELECT * FROM tl_spielerregister WHERE id=?")
			                            ->execute($this->spielerregister_id);

			// Adresse gefunden
			if($objPerson->numRows)
			{
				// Überschrift auf Platzhalter %s prüfen
				$pos = strpos($this->headline, '%s');
				if($pos !== false)
				{
					// Überschrift ändern
					$this->headline = sprintf($this->headline, $objPerson->firstname1.' '.$objPerson->surname1);
				}

				$this->Template->addImage = false;
				$this->Template->addBefore = false;

				// Add an image
				//if ($this->addImage)
				//{
				//	$figure = \System::getContainer()
				//		->get(\Contao\CoreBundle\Image\Studio\Studio::class)
				//		->createFigureBuilder()
				//		->from($this->singleSRC)
				//		->setSize($this->size)
				//		->setMetadata($this->objModel->getOverwriteMetadata())
				//		->enableLightbox((bool) $this->fullsize)
				//		->buildIfResourceExists();
                //
				//	if (null !== $figure)
				//	{
				//		$figure->applyLegacyTemplateData($this->Template, $this->imagemargin, $this->floating);
				//	}
				//}

				// Daten aus tl_content in das Template schreiben
				$this->Template->headline      = $this->headline;
				$this->Template->name          = $objPerson->firstname1.' '.$objPerson->surname1;
				$this->Template->lebensdaten   = $objPerson->hideLifedata ? '' : \Schachbulle\ContaoSpielerregisterBundle\Klassen\Helper::getLebensdaten($objPerson->birthday, $objPerson->birthplace, $objPerson->death, $objPerson->deathday, $objPerson->deathplace);
				$this->Template->kurzinfo      = $objPerson->shortinfo;
				$this->Template->langinfo      = $objPerson->longinfo;


			}
		}

		return;

	}

}