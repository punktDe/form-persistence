<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\ResourceManagement\PersistentResource;

class ValueFormattingProcessor implements ProcessorInterface
{

    public function convertFormData(array $formData, array $conversionDefinition): array
    {
        $convertedData = [];

        foreach ($formData as $fieldIdentifier => $fieldValue) {
            if ($fieldValue instanceof PersistentResource) {
                $convertedData[$fieldIdentifier] = $fieldValue->getFilename();
                continue;
            }

            if (is_array($fieldValue) && array_key_exists('date', $fieldValue)) {
                $convertedData[] = (new \DateTime($fieldValue['date']))
                    ->setTimezone(new \DateTimeZone($fieldValue['timezone']))
                    ->format('d.m.Y');
                continue;
            }

            if (is_array($fieldValue)) {
                continue;
            }

            $convertedData[$fieldIdentifier] = $fieldValue;
        }

        return $convertedData;
    }
}
