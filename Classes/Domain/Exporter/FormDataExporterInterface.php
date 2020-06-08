<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

interface FormDataExporterInterface
{
    /**
     * @param iterable $formDataItems
     * @param string $fileName
     */
    public function compileAndSend(iterable $formDataItems, string $fileName): void;
}
