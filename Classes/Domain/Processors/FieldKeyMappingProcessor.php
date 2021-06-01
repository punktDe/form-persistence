<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Exception\ProcessingException;

class FieldKeyMappingProcessor implements ProcessorInterface
{

    public function convertFormData(array $formData, ?ExportDefinitionInterface $exportDefinition): array
    {
        if (!$exportDefinition instanceof ExportDefinitionInterface) {
            return $formData;
        }

        if (empty($exportDefinition->getDefinition())) {
            return $formData;
        }

        $convertedData = [];
        foreach ($exportDefinition->getDefinition() as $formKey => $configuration) {

            if (!array_key_exists($formKey, $formData)) {
                throw new ProcessingException(sprintf('Could not find field %s in formData, available are "%s"', $formKey, implode(', ', array_keys($formData))), 1622033828);
            }

            $newKey = $configuration['changeKey'] ?? $formKey;
            $convertedData[$newKey] = $formData[$formKey];
        }

        return $convertedData;
    }
}
