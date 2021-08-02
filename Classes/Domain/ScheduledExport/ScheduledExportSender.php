<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\ScheduledExport;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\SwiftMailer\Message;
use PunktDe\Form\Persistence\Domain\Model\ScheduledExport;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;
use PunktDe\Form\Persistence\Domain\Repository\ScheduledExportRepository;
use PunktDe\Form\Persistence\Service\TemplateStringService;

class ScheduledExportSender
{

    /**
     * @Flow\Inject
     * @var FormDataRepository
     */
    protected $formDataRepository;

    /**
     * @Flow\Inject
     * @var ScheduledExportRepository
     */
    protected $scheduledExportRepository;

    /**
     * @Flow\InjectConfiguration(package="PunktDe.Form.Persistence", path="scheduledExport")
     * @var array
     */
    protected $scheduledExportConfiguration;

    public function sendScheduledExports(): void
    {
        foreach ($this->scheduledExportRepository->findAll() as $scheduledExport) {
            $this->sendScheduledExport($scheduledExport);
        }
    }

    protected function sendScheduledExport(ScheduledExport $scheduledExport): void
    {

//        $formData = $this->formDataRepository->findByFormIdentifierAndHash()
//
//        $mail = (new Message())
//            ->setFrom([$this->scheduledExportConfiguration['senderMailAddress'] => $this->scheduledExportConfiguration['senderName']])
//            ->setTo([$scheduledExport->getEmail() => $scheduledExport->getEmail()])
//            ->setSubject(TemplateStringService::processTemplate($this->scheduledExportConfiguration['subject']));


    }

}
