<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\ExportDefinition;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Domain\Model\FormData;

interface ExportDefinitionInterface
{
    public function isSuitableFor(FormData $formData): bool;

    public function getIdentifier(): string;

    public function getLabel(): string;

    public function getExporter(): string;

    public function getDefinition(): array;

    public function getFileNamePattern(): string;
}
