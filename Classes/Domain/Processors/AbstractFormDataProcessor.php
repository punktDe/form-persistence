<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Processors;

/*
 *  (c) 2022 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

abstract class AbstractFormDataProcessor implements ProcessorInterface
{
    protected array $options;

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
