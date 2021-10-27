<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\ScheduledExport;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Exception;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException;
use Neos\Flow\I18n\Exception\IndexOutOfBoundsException;
use Neos\Flow\I18n\Exception\InvalidFormatPlaceholderException;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\ObjectManagement\Exception\CannotBuildObjectException;
use Neos\Flow\ObjectManagement\Exception\UnknownObjectException;
use Neos\Flow\Persistence\Exception\InvalidQueryException;
use Neos\Flow\Utility\Algorithms;
use Neos\Flow\Utility\Environment;
use Neos\SwiftMailer\Message;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;
use Psr\Log\LoggerInterface;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionProvider;
use PunktDe\Form\Persistence\Domain\Exporter\ExporterFactory;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Model\ScheduledExport;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;
use PunktDe\Form\Persistence\Domain\Repository\ScheduledExportRepository;
use PunktDe\Form\Persistence\Exception\ConfigurationException;
use PunktDe\Form\Persistence\Service\TemplateStringService;
use Swift_Attachment;

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

    /**
     * @Flow\Inject
     * @var ExportDefinitionProvider
     */
    protected $exportDefinitionProvider;

    /**
     * @Flow\Inject
     * @var ExporterFactory
     */
    protected $exporterFactory;

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function initializeObject(): void
    {
        $this->formDataRepository->deactivateSecurityChecks();
    }

    public function sendScheduledExports(): void
    {
        $scheduledExports = $this->scheduledExportRepository->findAll();

        foreach ($scheduledExports as $scheduledExport) {
            $this->sendScheduledExport($scheduledExport);
        }

        $this->logger->info(sprintf('Found and processed %s scheduled exports.', $scheduledExports->count()), LogEnvironment::fromMethodName(__METHOD__));
    }

    /**
     * @param ScheduledExport $scheduledExport
     * @throws InvalidConfigurationTypeException
     * @throws IndexOutOfBoundsException
     * @throws InvalidFormatPlaceholderException
     * @throws CannotBuildObjectException
     * @throws UnknownObjectException
     * @throws InvalidQueryException
     * @throws \Neos\Flow\Utility\Exception
     * @throws FilesException
     * @throws ConfigurationException
     */
    protected function sendScheduledExport(ScheduledExport $scheduledExport): void
    {
        $formDataRepresentative = $this->formDataRepository->findLatestVersionOfForm($scheduledExport->getFormIdentifier());

        if (!$formDataRepresentative instanceof FormData) {
            $this->logger->info(sprintf('Form with identifier "%s" has no data - export was skipped', $scheduledExport->getFormIdentifier()), LogEnvironment::fromMethodName(__METHOD__));
            return;
        }

        $exportDefinition = $this->exportDefinitionProvider->getExportDefinitionByIdentifier($scheduledExport->getExportDefinitionIdentifier());
        $isSuitable = $exportDefinition->isSuitableFor($formDataRepresentative);
        $exportFilePath = $this->buildTemporaryFilePath();
        $fileName = TemplateStringService::processTemplate($exportDefinition->getFileNamePattern(), $formDataRepresentative->getFormIdentifier(), $formDataRepresentative->getHash(), $exportDefinition);

        $mail = (new Message())
            ->setFrom([$this->scheduledExportConfiguration['senderMailAddress'] => $this->scheduledExportConfiguration['senderName']])
            ->setTo([$scheduledExport->getEmail() => $scheduledExport->getEmail()])
            ->setSubject(TemplateStringService::processTemplate($this->scheduledExportConfiguration['subject'], $formDataRepresentative->getFormIdentifier(), $formDataRepresentative->getHash(), $exportDefinition));

        if (!$isSuitable) {
            $mail->setBody(
                $this->translator->translateById(
                    'mailBody.exportDefinitionNotSuitable',
                    [
                        'formIdentifier' => $formDataRepresentative->getFormIdentifier(),
                        'formVersion' => $formDataRepresentative->getHash(),
                        'exportDefinitionIdentifier' => $exportDefinition->getLabel()
                    ],
                    null,
                    null,
                    'Main',
                    'PunktDe.Form.Persistence'
                )
            );

            $this->logger->warning(sprintf('Scheduled Export failed, as the exportDefinition "%s" was not suitable for version "%s" of form "%s"', $exportDefinition->getIdentifier(), $formDataRepresentative->getHash(), $formDataRepresentative->getFormIdentifier()), LogEnvironment::fromMethodName(__METHOD__));

        } else {
            $formDataCollection = $this->formDataRepository->findByFormIdentifierAndHash($formDataRepresentative->getFormIdentifier(), $formDataRepresentative->getHash());

            $formDataItems = array_map(static function (FormData $formData) use ($exportDefinition) {
                return $formData->getProcessedFormData($exportDefinition);
            }, $formDataCollection->toArray());

            $this->exporterFactory->makeExporterByExportDefinition($exportDefinition)->compileAndSave($formDataItems, $exportFilePath);
            $attachment = Swift_Attachment::fromPath($exportFilePath)->setFilename($fileName);
            $mail->attach($attachment);

            $mail->setBody(
                $this->translator->translateById(
                    'mailBody.successfulExport',
                    [
                        'entryCount' => $formDataCollection->count(),
                        'latestEntryDate' => $formDataRepresentative->getDate()->format('Y-m-d H:i:s'),
                        'formIdentifier' => $formDataRepresentative->getFormIdentifier(),
                        'exportDefinitionIdentifier' => $exportDefinition->getLabel()
                    ],
                    null,
                    null,
                    'Main',
                    'PunktDe.Form.Persistence'
                )
            );

            $this->logger->info(sprintf('Successfully sent scheduled export %s with %s items', $scheduledExport->getFormIdentifier(), $formDataCollection->count()), LogEnvironment::fromMethodName(__METHOD__));
        }

        $mail->send();

        if (file_exists($exportFilePath)) {
            unlink($exportFilePath);
        }
    }

    /**
     * @return string
     * @throws \Neos\Flow\Utility\Exception
     * @throws FilesException
     * @throws Exception
     */
    protected function buildTemporaryFilePath(): string
    {
        return Files::concatenatePaths([$this->environment->getPathToTemporaryDirectory(), 'PunktDe_Form_Persistence_Export_' . Algorithms::generateRandomString(13)]);
    }
}
