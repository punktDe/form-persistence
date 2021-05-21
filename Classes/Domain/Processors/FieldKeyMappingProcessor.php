<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;

class FieldKeyMappingProcessor implements ProcessorInterface
{

    public function convertFormData(array $formData, ?ExportDefinitionInterface $exportDefinition): array
    {
        if (!$exportDefinition instanceof ExportDefinitionInterface) {
            return $formData;
        }

        $convertedData = [];
        foreach ($exportDefinition->getDefinition() as $formKey => $configuration) {
            $newKey = $configuration['changeKey'] ?? $formKey;
            $convertedData[$newKey] = $formData[$formKey];
        }

        return $convertedData;
    }
}
