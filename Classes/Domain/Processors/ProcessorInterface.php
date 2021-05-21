<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */


use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;

interface ProcessorInterface
{
    public function convertFormData(array $formData, ?ExportDefinitionInterface $exportDefinition): array;
}
