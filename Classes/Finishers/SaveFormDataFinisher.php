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

    /**
     * @Flow\Inject
     * @var FormDataRepository
     */
    protected $formDataRepository;

    /**
     * @Flow\InjectConfiguration(package="PunktDe.Form.Persistence", path="finisher.excludedFormTypes")
     * @var array
     */
    protected $excludedFormTypes = [];

    /**
     * @throws IllegalObjectTypeException
     */
    protected function executeInternal(): void
    {
        $formRuntime = $this->finisherContext->getFormRuntime();

        $fieldValues = $this->finisherContext->getFormValues();
        $formFieldsData = [];
        $fieldIdentifiersString = '';

        $excludedFormTypes = array_keys(array_filter($this->excludedFormTypes));

        foreach ($fieldValues as $identifier => $fieldValue) {

            if (!$formRuntime->getFormDefinition()->getElementByIdentifier($identifier) instanceof AbstractFormElement) {
                continue;
            }

            if (in_array($formRuntime->getFormDefinition()->getElementByIdentifier($identifier)->getType(), $excludedFormTypes, true)) {
                continue;
            }

            $formFieldsData[$identifier] = $fieldValue;
            $fieldIdentifiersString .= $identifier;
        }

        $formData = new FormData();

        $formData->setFormIdentifier($formRuntime->getIdentifier());
        $formData->setHash(sha1($fieldIdentifiersString));
        $formData->setFormData($formFieldsData);
        $formData->setDate(new \DateTime());
        $formData->setSiteName($this->options['siteName'] ?? '');
        $formData->setContentDimension($this->options['contentDimension'] ?? '');

        $this->formDataRepository->add($formData);
    }
}
