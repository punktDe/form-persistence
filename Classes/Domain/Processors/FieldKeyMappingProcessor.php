<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

class FieldKeyMappingProcessor implements ProcessorInterface
{

    public function convertFormData(array $formData, array $conversionDefinition): array
    {
        $convertedData = [];
        foreach ($conversionDefinition as $formKey => $configuration) {
            $newKey = $configuration['changeKey'] ?? $formKey;
            $convertedData[$newKey] = $formData[$formKey];
        }

        return $convertedData;
    }
}
