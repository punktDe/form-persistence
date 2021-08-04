<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Command;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use PunktDe\Form\Persistence\Domain\ScheduledExport\ScheduledExportSender;

class FormPersistenceCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var ScheduledExportSender
     */
    protected $scheduledExportSender;

    public function sendExportCommand(): void
    {
        $this->scheduledExportSender->sendScheduledExports();
    }
}
