<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

interface FormDataExporterInterface
{

    public function setFileName(string $fileName): void;

    public function setOptions(array $options): void;

    /**
     * @param iterable $formDataItems
     */
    public function compileAndSend(iterable $formDataItems): void;

    /**
     * @param iterable $formDataItems
     * @return string The path to the temporary file
     */
    public function compileToTemporaryFile(iterable $formDataItems);

}
