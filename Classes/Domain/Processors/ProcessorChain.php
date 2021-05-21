<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;

class ProcessorChain implements ProcessorInterface
{
    /**
     * @Flow\Inject
     * @var FieldKeyMappingProcessor
     */
    protected $fieldKeyMappingProcessor;

    /**
     * @Flow\Inject
     * @var ValueFormattingProcessor
     */
    protected $valueFormattingProcessor;

    public function convertFormData(array $formData, ?ExportDefinitionInterface $exportDefinition): array
    {
        $convertedFormData = $this->valueFormattingProcessor->convertFormData($formData, $exportDefinition);
        $convertedFormData = $this->fieldKeyMappingProcessor->convertFormData($convertedFormData, $exportDefinition);

        return $convertedFormData;
    }
}
