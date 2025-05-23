<?php

declare(strict_types=1);

namespace PunktDe\Form\Persistence\Finishers;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Form\Core\Model\AbstractFinisher;
use Neos\Form\Core\Model\AbstractFormElement;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;

class SaveFormDataFinisher extends AbstractFinisher
{

    #[Flow\Inject]
    protected FormDataRepository $formDataRepository;
    #[Flow\InjectConfiguration(path: 'finisher.excludedFormTypes', package: 'PunktDe.Form.Persistence')]
    protected array $excludedFormTypes = [];

    /**
     * @throws IllegalObjectTypeException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function executeInternal(): void
    {
        $this->saveFormData();
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function saveFormData(): void
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $fieldValues = $this->finisherContext->getFormValues();

        $formFieldsData = [];
        $formFieldIdentifiers = [];
        $fieldIdentifiersString = '';

        $excludedFormTypes = array_keys(array_filter($this->excludedFormTypes));

        foreach ($formRuntime->getPages() as $page) {
            foreach ($page->getElementsRecursively() as $renderable) {
                $formFieldIdentifiers[] = $renderable->getIdentifier();
            }
        }

        foreach ($formFieldIdentifiers as $identifier) {

            if (!$formRuntime->getFormDefinition()->getElementByIdentifier($identifier) instanceof AbstractFormElement) {
                continue;
            }

            if (in_array($formRuntime->getFormDefinition()->getElementByIdentifier($identifier)->getType(), $excludedFormTypes, true)) {
                continue;
            }

            $formFieldsData[$identifier] = $fieldValues[$identifier] ?? '';
            $fieldIdentifiersString .= $identifier;
        }

        $formData = new FormData();

        $formData->setFormIdentifier($this->options['formIdentifier'] ?? $formRuntime->getIdentifier());
        $formData->setHash(sha1($fieldIdentifiersString));
        $formData->setFormData($formFieldsData);
        $formData->setDate(new \DateTime());
        $formData->setSiteName($this->options['siteName'] ?? '');
        $formData->setContentDimensions($this->options['contentDimensions'] ?? []);

        $this->formDataRepository->add($formData);
    }
}
