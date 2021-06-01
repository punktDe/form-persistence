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

class FlattenArrayProcessor implements ProcessorInterface
{

    public function convertFormData(array $formData, ?ExportDefinitionInterface $exportDefinition): array
    {
        $convertedData = [];
        foreach ($formData as $fieldIdentifier => $fieldValue) {

            if (is_array($fieldValue)) {
                $convertedData = array_merge($convertedData, ArrayFlattenService::flattenArray($fieldValue, $fieldIdentifier));
                continue;
            }

            $convertedData[$fieldIdentifier] = $fieldValue;
        }

        return $convertedData;
    }
}
