<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Command;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use PunktDe\Form\Persistence\Domain\FormDataCleanup\FormDataCleanupService;
use PunktDe\Form\Persistence\Domain\ScheduledExport\ScheduledExportSender;

class FormPersistenceCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var ScheduledExportSender
     */
    protected $scheduledExportSender;

    /**
     * @Flow\Inject
     * @var FormDataCleanupService
     */
    protected $formDataCleanupService;

    /**
     * Export and send form data
     */
    public function sendExportCommand(): void
    {
        $this->scheduledExportSender->sendScheduledExports();
    }

    /**
     * Delete form data older than the configured time interval
     * @throws \Exception
     */
    public function cleanUpFormDataCommand(): void
    {
        $count = $this->formDataCleanupService->cleanupOldFormData();
        $this->outputLine(sprintf('Removed %d form data entries', $count));
    }
}
