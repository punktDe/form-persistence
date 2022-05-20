<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2022 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Domain\Model\FormData;

class AddMetaDataProcessor extends AbstractFormDataProcessor
{

    public function process(FormData $formData, array $formValues, ?ExportDefinitionInterface $exportDefinition): array
    {
        if (empty($this->options['fields'] ?? [])) {
            return $formValues;
        }

        $metaData = [];

        foreach ($this->options['fields'] as $formDataFieldName => $fieldName) {
            $getter = 'get' . ucfirst($formDataFieldName);
            if (method_exists($formData, $getter)) {
                $metaData[$fieldName] = $formData->$getter();
            }
        }

        return array_merge($metaData, $formValues);
    }
}
