<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Service;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Context as SecurityContext;
use Psr\Log\LoggerInterface;
use PunktDe\Form\Persistence\Domain\Model\ScheduledExport;
use PunktDe\Form\Persistence\Domain\Repository\ScheduledExportRepository;

class ScheduledExportService
{

    /**
     * @Flow\Inject
     * @var ScheduledExportRepository
     */
    protected $scheduledExportRepository;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function saveScheduledExportDefinition(string $formIdentifier, string $recipientEmail, string $exportDefinitionIdentifier): void
    {
        $scheduledExport = $this->scheduledExportRepository->findOneByFormIdentifier($formIdentifier);

        if ($scheduledExport instanceof ScheduledExport) {
            $this->scheduledExportRepository->update($scheduledExport);
        } else {
            $scheduledExport = new ScheduledExport();
            $this->scheduledExportRepository->add($scheduledExport);
        }

        $scheduledExport
            ->setFormIdentifier($formIdentifier)
            ->setEmail($recipientEmail)
            ->setExportDefinitionIdentifier($exportDefinitionIdentifier);

        $this->logger->info(sprintf('Scheduled Export Definition for form %s was defined by user %s with recipient %s and export definition identifier %s', $formIdentifier, $this->getCurrentBackendUser(), $recipientEmail, $exportDefinitionIdentifier), LogEnvironment::fromMethodName(__METHOD__));
    }

    public function removeScheduledExportDefinitionIfExists(string $formIdentifier): void
    {
        $scheduledExport = $this->scheduledExportRepository->findOneByFormIdentifier($formIdentifier);
        if ($scheduledExport instanceof ScheduledExport) {
            $this->scheduledExportRepository->remove($scheduledExport);
        }
        $this->logger->info(sprintf('Scheduled Export Definition for form %s was removed by user %s', $formIdentifier, $this->getCurrentBackendUser()), LogEnvironment::fromMethodName(__METHOD__));
    }

    /**
     * @return string
     */
    protected function getCurrentBackendUser(): string
    {
        return $this->securityContext->getAccount() instanceof Account ? $this->securityContext->getAccount()->getAccountIdentifier() : 'unknown';
    }
}
