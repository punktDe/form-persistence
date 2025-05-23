<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Fusion\Eel;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionProvider;
use PunktDe\Form\Persistence\Domain\Model\FormData;

class ExportDefinitionHelper implements ProtectedContextAwareInterface
{
    #[Flow\Inject]
    protected ExportDefinitionProvider $exportDefinitionProvider;

    public function getSuitableExportDefinitions(FormData $formData): array
    {
        return $this->exportDefinitionProvider->findSuitableExportDefinitionsForFormData($formData);
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
