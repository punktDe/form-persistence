<?php

namespace PunktDe\Form\Persistence\Domain\Exporter;

use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\Utility\Algorithms;
use Neos\Flow\Utility\Environment;
use Neos\Utility\Files;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Log\LoggerInterface;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Domain\Exporter\FormDataExporterInterface;
use PunktDe\Form\Persistence\Domain\Model\FormData;

class ZipArchiveExporter extends SpreadSheetExporter
{

    protected string $fileName = 'FormData.zip';

    public function __construct(
        private readonly Environment $environment,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param iterable $formDataItems
     * @param ExportDefinitionInterface $exportDefinition
     * @inheritDoc
     */
    public function compileAndSend(array $formDataItems, ExportDefinitionInterface $exportDefinition): void
    {
        $this->sendHeader();

        $temporaryFileName = Files::concatenatePaths([$this->environment->getPathToTemporaryDirectory(), uniqid()]);
        $this->createZIPArchive($temporaryFileName, $formDataItems, $exportDefinition);

        readfile($temporaryFileName);
        unlink($temporaryFileName);
    }

    public function compileAndSave(array $formDataItems, string $filePath, ExportDefinitionInterface $exportDefinition): void
    {
        $this->createZIPArchive($filePath, $formDataItems, $exportDefinition);
    }

    private function createZIPArchive(string $zipFilePath, array $formDataItems, ExportDefinitionInterface $exportDefinition): void
    {
        $zip = new \ZipArchive();

        $success = $zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if (!$success) {
            throw new \RuntimeException('Could not open file at ' . $zipFilePath);
        }
        /** @var FormData $formDataItem */
        foreach ($formDataItems as $formDataItem) {

            foreach ($formDataItem->getFormData() as $key => $fieldValue) {
                if ($fieldValue instanceof PersistentResource) {
                    $zip->addFile($fieldValue->createTemporaryLocalCopy(), $fieldValue->getFilename());
                }
            }
        }

        $processedFormDataItems = array_map(static function (FormData $formData) use ($exportDefinition) {
            return $formData->getProcessedFormData($exportDefinition);
        }, $formDataItems);
        $writer = IOFactory::createWriter($this->compileXLS($processedFormDataItems), IOFactory::WRITER_XLSX);

        $xlsFilePath = Files::concatenatePaths([$this->environment->getPathToTemporaryDirectory(), 'PunktDe_Form_Persistence_Export_' . Algorithms::generateRandomString(13)]);
        $writer->save($xlsFilePath);

        $zip->addFile($xlsFilePath, str_replace('.zip', '.xlsx', $this->fileName));
        $zip->close();

        $this->logger->debug(sprintf('Wrote zip file to path %s - %s', $zipFilePath, file_exists($zipFilePath)));
    }
}
