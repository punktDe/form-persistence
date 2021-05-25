<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Model;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;

/**
 * @Flow\Entity
 */
class ExportDefinition implements ExportDefinitionInterface
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $exporter;

    /**
     * @var array
     * @ORM\Column(type="flow_json_array")
     */
    protected $definition;

    /**
     * @var string
     */
    protected $fileNamePattern = '';

    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;


    public function isSuitableFor(?FormData $formData): bool
    {
        if ($formData === null) {
            return false;
        }
        return count(array_intersect(array_keys($this->definition), $formData->getProcessedFieldNames())) === count($this->definition);
    }

    public function getIdentifier(): string
    {
        return $this->persistenceManager->getIdentifierByObject($this);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return ExportDefinition
     */
    public function setLabel(string $label): ExportDefinition
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getExporter(): string
    {
        return $this->exporter;
    }

    /**
     * @param string $exporter
     * @return ExportDefinition
     */
    public function setExporter(string $exporter): ExportDefinition
    {
        $this->exporter = $exporter;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileNamePattern(): string
    {
        return $this->fileNamePattern;
    }

    /**
     * @param string $fileNamePattern
     * @return ExportDefinition
     */
    public function setFileNamePattern(string $fileNamePattern): ExportDefinition
    {
        $this->fileNamePattern = $fileNamePattern;
        return $this;
    }

    /**
     * @return array
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * @param array $definition
     * @return ExportDefinition
     */
    public function setDefinition(array $definition): ExportDefinition
    {
        $this->definition = $definition;
        return $this;
    }
}
