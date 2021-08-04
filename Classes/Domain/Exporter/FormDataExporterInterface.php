<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

interface FormDataExporterInterface
{

    public function setFileName(string $fileName): FormDataExporterInterface;

    public function setOptions(array $options): FormDataExporterInterface;

    /**
     * @param iterable $formDataItems
     */
    public function compileAndSend(iterable $formDataItems): void;

    /**
     * @param iterable $formDataItems
     * @param string $filePath
     */
    public function compileAndSave(iterable $formDataItems, string $filePath): void;

}
