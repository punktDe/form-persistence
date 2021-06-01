<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\ResourceManagement\PersistentResource;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Service\ArrayFlattenService;

class ValueFormattingProcessor implements ProcessorInterface
{

    public function convertFormData(array $formData, ?ExportDefinitionInterface $exportDefinition): array
    {
        $convertedData = [];
        foreach ($formData as $fieldIdentifier => $fieldValue) {
            if ($fieldValue instanceof PersistentResource) {
                $convertedData[$fieldIdentifier] = $fieldValue->getFilename();
                continue;
            }

            if (is_array($fieldValue) && array_key_exists('date', $fieldValue)) {
                $convertedData[$fieldIdentifier] = (new \DateTime($fieldValue['date']))
                    ->setTimezone(new \DateTimeZone($fieldValue['timezone']))
                    ->format('d.m.Y');
                continue;
            }

            $convertedData[$fieldIdentifier] = $fieldValue;
        }

        return $convertedData;
    }
}
