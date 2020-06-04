<?php

declare(strict_types=1);

namespace PunktDe\Form\Persistence\Finishers;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Neos\Form\Core\Model\AbstractFinisher;

class SaveFromDataFinisher extends AbstractFinisher
{

    protected function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $fieldValues = $this->finisherContext->getFormValues();
    }
}
