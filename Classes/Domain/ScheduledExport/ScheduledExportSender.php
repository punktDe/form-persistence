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
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
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
     * @throws FilesException
     * @throws \Neos\Flow\Utility\Exception
     */
    protected function sendScheduledExport(ScheduledExport $scheduledExport): void
    {
        $formDataRepresentative = $this->formDataRepository->findLatestVersionOfForm($scheduledExport->getFormIdentifier());
        $exportFilePath = $this->buildTemporaryFilePath();

        if (!$formDataRepresentative instanceof FormData) {
            $this->logger->info(sprintf('Form with identifier "%s" has no data - export was skipped', $scheduledExport->getFormIdentifier()), LogEnvironment::fromMethodName(__METHOD__));
            return;
        }

        $mail = (new Message())
            ->setFrom([$this->scheduledExportConfiguration['senderMailAddress'] => $this->scheduledExportConfiguration['senderName']])
            ->setTo([$scheduledExport->getEmail() => $scheduledExport->getEmail()]);

        try {
            $exportDefinition = $this->exportDefinitionProvider->getExportDefinitionByIdentifier($scheduledExport->getExportDefinitionIdentifier());
            $isSuitable = $exportDefinition->isSuitableFor($formDataRepresentative);
            $fileName = TemplateStringService::processTemplate($exportDefinition->getFileNamePattern(), $formDataRepresentative->getFormIdentifier(), $formDataRepresentative->getHash(), $exportDefinition);

            $mail->setSubject(TemplateStringService::processTemplate($this->scheduledExportConfiguration['subject'], $formDataRepresentative->getFormIdentifier(), $formDataRepresentative->getHash(), $exportDefinition));

            if ($isSuitable) {
                $this->prepareExportMail($formDataRepresentative, $exportDefinition, $exportFilePath, $fileName, $mail);
            } else {
                $this->prepareExportDefinitionNotSuitableMail($mail, $formDataRepresentative, $exportDefinition);
            }
        } catch (\Exception $exception) {
            $mail->setSubject('An error occured while exporting latest data from ' . $formDataRepresentative->getFormIdentifier());
            $this->prepareErrorOnExportMail($mail, $formDataRepresentative, $exception);
        } finally {


            $mail->send();

            if (file_exists($exportFilePath)) {
                unlink($exportFilePath);
            }
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

    /**
     * @param Message $mail
     * @param FormData $formDataRepresentative
     * @param ExportDefinitionInterface $exportDefinition
     * @throws IndexOutOfBoundsException
     * @throws InvalidFormatPlaceholderException
     */
    protected function prepareExportDefinitionNotSuitableMail(Message $mail, FormData $formDataRepresentative, ExportDefinitionInterface $exportDefinition): void
    {
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
    }

    /**
     * @param FormData $formDataRepresentative
     * @param ExportDefinitionInterface $exportDefinition
     * @param string $exportFilePath
     * @param string $fileName
     * @param Message $mail
     * @throws CannotBuildObjectException
     * @throws ConfigurationException
     * @throws IndexOutOfBoundsException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidFormatPlaceholderException
     * @throws InvalidQueryException
     * @throws UnknownObjectException
     */
    protected function prepareExportMail(FormData $formDataRepresentative, ExportDefinitionInterface $exportDefinition, string $exportFilePath, string $fileName, Message $mail): void
    {
        $formDataCollection = $this->formDataRepository->findByFormProperties($formDataRepresentative->getFormIdentifier(), $formDataRepresentative->getHash(), $formDataRepresentative->getSiteName(), $formDataRepresentative->getDimensionsHash());

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

        $this->logger->info(sprintf('Successfully sent scheduled export %s with %s items', $formDataRepresentative->getFormIdentifier(), $formDataCollection->count()), LogEnvironment::fromMethodName(__METHOD__));
    }

    private function prepareErrorOnExportMail(Message $mail, FormData $formDataRepresentative, \Exception $exception): void
    {
        $mail->setBody(
            $this->translator->translateById(
                'mailBody.errorWhileExport',
                [
                    'formIdentifier' => $formDataRepresentative->getFormIdentifier(),
                    'formVersion' => $formDataRepresentative->getHash(),
                    'errorCode' => $exception->getCode(),
                    'errorMessage' => $exception->getMessage(),
                ],
                null,
                null,
                'Main',
                'PunktDe.Form.Persistence'
            )
        );

        $this->logger->error(sprintf('Scheduled Export failed for form identifier %s with code %s, with exception %s (%s)', $formDataRepresentative->getHash(), $formDataRepresentative->getFormIdentifier(), $exception->getMessage(), $exception->getCode()), LogEnvironment::fromMethodName(__METHOD__));
    }
}
