<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\DataSources;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use \Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionProvider;

class ExportDefinitionDataSource extends AbstractDataSource
{
    protected static $identifier = 'punktde-form-persistence-export-definition';

    #[Flow\Inject]
    protected ExportDefinitionProvider $exportDefinitionProvider;

    /**
     * Get data
     *
     * The return value must be JSON serializable data structure.
     *
     * @param Node|null $node The node that is currently edited (optional)
     * @param array $arguments Additional arguments (key / value)
     * @return mixed JSON serializable data
     */
    public function getData(?Node $node = null, array $arguments = [])
    {
        $data = [];

        foreach ($this->exportDefinitionProvider->findExportDefinitions() as $exportDefinition) {
            $data[] = ['value' => $exportDefinition->getIdentifier(), 'label' => $exportDefinition->getLabel()];
        }

        return $data;
    }
}
