<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\ExportDefinition;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Domain\Model\FormData;

class StaticExportDefinition implements ExportDefinitionInterface
{
    /**
     * @var string
     */
    protected $identifier;

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
     */
    protected $definition;

    /**
     * @var string
     */
    protected $fileNamePattern;

    /**
     * StaticExportDefinition constructor.
     * @param string $identifier
     * @param string $label
     * @param string $exporter
     * @param string $fileNamePattern
     * @param array $definition
     */
    public function __construct(string $identifier, string $label, string $exporter, string $fileNamePattern, array $definition)
    {
        $this->identifier = $identifier;
        $this->label = $label;
        $this->exporter = $exporter;
        $this->definition = $definition;
        $this->fileNamePattern = $fileNamePattern;
    }

    public function isSuitableFor(FormData $formData): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getExporter(): string
    {
        return $this->exporter;
    }

    /**
     * @return array
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * @return string
     */
    public function getFileNamePattern(): string
    {
        return $this->fileNamePattern;
    }
}
