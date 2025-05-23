<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020-2025 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;

interface FormDataExporterInterface
{

    public function setFileName(string $fileName): FormDataExporterInterface;

    public function setOptions(array $options): FormDataExporterInterface;

    public function compileAndSend(array $formDataItems, ExportDefinitionInterface $exportDefinition): void;

    public function compileAndSave(array $formDataItems, string $filePath, ExportDefinitionInterface $exportDefinition): void;

}
