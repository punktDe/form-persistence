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
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Exception\ConfigurationException;

class ProcessorChain
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
    protected ?array $processorChain = null;

    /**
     * @param FormData $formData
     * @param array $formValues
     * @param ExportDefinitionInterface|null $exportDefinition
     * @return array
     * @throws ConfigurationException
     */
    public function convertFormData(FormData $formData, array $formValues, ?ExportDefinitionInterface $exportDefinition): array
    {
        if ($this->processorChain === null) {
            $this->initializeProcessorChain();
        }

        foreach ($this->processorChain as $processor) {
            $formValues = $processor->process($formData, $formValues, $exportDefinition);
        }

        return $formValues;
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

            if (isset($processorSetting['options']) && is_array($processorSetting['options'])) {
                $processor->setOptions($processorSetting['options']);
            }

            $this->processorChain[$processorIdentifier] = $processor;
        }
    }
}
