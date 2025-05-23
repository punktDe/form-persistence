<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\ExportDefinition;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use PunktDe\Form\Persistence\Domain\Model\ExportDefinition;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Repository\ExportDefinitionRepository;

/**
 * @Flow\Scope("singleton")
 */
class ExportDefinitionProvider
{
    #[Flow\Inject]
    protected ExportDefinitionRepository $exportDefinitionRepository;

    /**
     * @var ExportDefinition[]
     */
    protected array $dynamicExportDefinitions = [];

    #[Flow\InjectConfiguration(path: 'exportDefinitions', package: 'PunktDe.Form.Persistence')]
    protected array $staticExportDefinitions;

    public function initializeObject(): void
    {
        /** @var ExportDefinition $exportDefinition */
        foreach ($this->exportDefinitionRepository->findAll() as $exportDefinition) {
            $this->dynamicExportDefinitions[$exportDefinition->getIdentifier()] = $exportDefinition;
        }
    }

    public function findExportDefinitions(): array
    {
        $exportDefinitions = $this->dynamicExportDefinitions;

        foreach ($this->staticExportDefinitions as $key => $staticExportDefinition) {
            $exportDefinitions[$key] = $this->buildStaticExportDefinition($key, $staticExportDefinition);
        }

        usort($exportDefinitions, static function (ExportDefinitionInterface $a, ExportDefinitionInterface $b) {
            return strcmp($a->getLabel(), $b->getLabel());
        });

        return $exportDefinitions;
    }

    /**
     * @param FormData $formData
     * @return ExportDefinitionInterface[]
     */
    public function findSuitableExportDefinitionsForFormData(FormData $formData): array
    {
        $exportDefinitions = array_filter($this->dynamicExportDefinitions, static function (ExportDefinition $exportDefinition) use ($formData) {
            return $exportDefinition->isSuitableFor($formData);
        });

        foreach ($this->staticExportDefinitions as $key => $staticExportDefinition) {
            $exportDefinitions[$key] = $this->buildStaticExportDefinition($key, $staticExportDefinition);
        }

        usort($exportDefinitions, static function (ExportDefinitionInterface $a, ExportDefinitionInterface $b) {
            return strcmp($a->getLabel(), $b->getLabel());
        });

        return $exportDefinitions;
    }

    public function getExportDefinitionByIdentifier(string $exportDefinitionIdentifier): ExportDefinitionInterface
    {
        if (array_key_exists($exportDefinitionIdentifier, $this->dynamicExportDefinitions)) {
            return $this->dynamicExportDefinitions[$exportDefinitionIdentifier];
        }

        if (array_key_exists($exportDefinitionIdentifier, $this->staticExportDefinitions)) {
            return $this->buildStaticExportDefinition($exportDefinitionIdentifier, $this->staticExportDefinitions[$exportDefinitionIdentifier]);
        }

        throw new \RuntimeException(sprintf('No export definition with key %s has been defined', $exportDefinitionIdentifier), 1621407631);
    }

    private function buildStaticExportDefinition(string $key, $staticExportDefinition): StaticExportDefinition
    {
        return new StaticExportDefinition(
            $key,
            $staticExportDefinition['label'] ?? $key,
            $staticExportDefinition['exporter'],
            $staticExportDefinition['fileNamePattern'] ?? '',
            $staticExportDefinition['definition'] ?? []
        );
    }
}
