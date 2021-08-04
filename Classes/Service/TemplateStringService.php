<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Service;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;

class TemplateStringService
{

    public static function processTemplate(string $pattern, string $formIdentifier, string $formVersionHash, ExportDefinitionInterface $exportDefinition): string
    {
        return str_replace(
            ['{formIdentifier}', '{currentDate}', '{exportDefinitionIdentifier}', '{formVersionHash}'],
            [$formIdentifier, date('Y-m-d_his'), $exportDefinition->getIdentifier(), $formVersionHash],
            $pattern
        );
    }
}
