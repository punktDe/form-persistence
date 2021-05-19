<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManager;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Exception\ConfigurationException;

class ExporterFactory
{
    /**
     * @Flow\Inject
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @Flow\InjectConfiguration(package="PunktDe.Form.Persistence", path="exporter")
     * @var array
     */
    protected $exporterSettings;

    /**
     * @param ExportDefinitionInterface $exportDefinition
     * @return FormDataExporterInterface
     * @throws ConfigurationException
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \Neos\Flow\ObjectManagement\Exception\CannotBuildObjectException
     * @throws \Neos\Flow\ObjectManagement\Exception\UnknownObjectException
     */
    public function makeExporterByExportDefinition(ExportDefinitionInterface $exportDefinition): FormDataExporterInterface
    {
        if (!array_key_exists($exportDefinition->getExporter(), $this->exporterSettings)) {
            throw new ConfigurationException(sprintf('An exporter with key %s was not found in the settings. Available are %s', $exportDefinition->getExporter(), implode(', ', array_keys($this->exporterSettings))), 1621408069);
        }

        if (!isset($this->exporterSettings[$exportDefinition->getExporter()]['className'])) {
            throw new ConfigurationException('No className was defined for exporter configuration ' . $exportDefinition->getExporter(), 1621408219);
        }

        $exporterClassName = $this->exporterSettings[$exportDefinition->getExporter()]['className'];

        $exporter = $this->objectManager->get($exporterClassName);

        if (!$exporter instanceof FormDataExporterInterface) {
            throw new \RuntimeException(sprintf('The exporter %s mus implement interface %s', $exporterClassName, FormDataExporterInterface::class), 1621408372);
        }

        $exporter->setOptions($this->exporterSettings[$exportDefinition->getExporter()]['options'] ?? []);
        return $exporter;
    }
}
