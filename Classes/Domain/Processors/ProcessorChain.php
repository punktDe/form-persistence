<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Utility\PositionalArraySorter;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Exception\ConfigurationException;

class ProcessorChain implements ProcessorInterface
{

    /**
     * @Flow\Inject
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @Flow\InjectConfiguration(package="PunktDe.Form.Persistence", path="processorChain")
     * @var array
     */
    protected $processorChainConfiguration = [];

    /**
     * @var ProcessorInterface[]
     */
    protected $processorChain = null;

    public function convertFormData(array $formData, ?ExportDefinitionInterface $exportDefinition): array
    {
        if($this->processorChain === null) {
            $this->initializeProcessorChain();
        }

        foreach ($this->processorChain as $processorIdentifier => $processor) {
            $formData = $processor->convertFormData($formData, $exportDefinition);
        }
        return $formData;
    }

    /**
     * @throws ConfigurationException
     */
    private function initializeProcessorChain(): void
    {
        $sortedProcessors = (new PositionalArraySorter($this->processorChainConfiguration))->toArray();

        foreach ($sortedProcessors as $processorIdentifier => $processorSetting) {
            if (!isset($processorSetting['class'])) {
                throw new ConfigurationException(sprintf('No class is given for processor %s', $processorIdentifier), 1621947877);
            }

            if (!class_exists($processorSetting['class'])) {
                throw new ConfigurationException(sprintf('The class %s for processor %s does not exist', $processorSetting['class'], $processorIdentifier), 1621947878);
            }

            $processor = new $processorSetting['class']();
            if (!$processor instanceof ProcessorInterface) {
                throw new ConfigurationException(sprintf('The processor of class %s does not implement interface %s', $processorSetting['class'], ProcessorInterface::class), 1621947879);
            }

            $this->processorChain[$processorIdentifier] = $processor;
        }
    }
}
