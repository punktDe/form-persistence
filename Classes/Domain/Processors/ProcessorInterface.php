<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */


use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Domain\Model\FormData;

interface ProcessorInterface
{
    public function setOptions(array $options): void;

    public function process(FormData $formData, array $formValues, ?ExportDefinitionInterface $exportDefinition): array;
}
