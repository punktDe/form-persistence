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
     * @throws IllegalObjectTypeException
     */
    protected function executeInternal(): void
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $fieldValues = $this->finisherContext->getFormValues();
        $formFieldsData = [];
        $fieldIdentifiersString = '';

        foreach ($fieldValues as $identifier => $fieldValue) {
            if ($formRuntime->getFormDefinition()->getElementByIdentifier($identifier) instanceof AbstractFormElement) {
                $formFieldsData[$identifier] = $fieldValue;
                $fieldIdentifiersString .= $identifier;
            }
        }

        $formData = new FormData();

        $formData->setFormIdentifier($formRuntime->getIdentifier());
        $formData->setHash(sha1($fieldIdentifiersString));
        $formData->setFormData($formFieldsData);

        $this->formDataRepository->add($formData);
    }
}
