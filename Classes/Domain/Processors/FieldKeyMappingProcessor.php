<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Exception\ProcessingException;

class FieldKeyMappingProcessor extends AbstractFormDataProcessor implements ProcessorInterface
{

    public function process(FormData $formData, array $formValues, ?ExportDefinitionInterface $exportDefinition): array
    {
        if (!$exportDefinition instanceof ExportDefinitionInterface) {
            return $formValues;
        }

        if (empty($exportDefinition->getDefinition())) {
            return $formValues;
        }

        $convertedData = [];
        foreach ($exportDefinition->getDefinition() as $formKey => $configuration) {

            if (!array_key_exists($formKey, $formValues)) {
                throw new ProcessingException(sprintf('Could not find field %s in formData, available are "%s"', $formKey, implode(', ', array_keys($formValues))), 1622033828);
            }

            $newKey = $configuration['changeKey'] ?? $formKey;
            $convertedData[$newKey] = $formValues[$formKey];
        }

        return $convertedData;
    }
}
