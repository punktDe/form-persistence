<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020-2025 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use League\Csv\Writer;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Domain\Model\FormData;

class CsvExporter implements FormDataExporterInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $fileName = 'FormData.csv';

    public function setFileName(string $fileName): FormDataExporterInterface
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function setOptions(array $options): FormDataExporterInterface
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param iterable $formDataItems
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function compileAndSend(array $formDataItems, ExportDefinitionInterface $exportDefinition): void
    {
       $processedFormDataItems = array_map(static function (FormData $formData) use ($exportDefinition) {
            return $formData->getProcessedFormData($exportDefinition);
        }, $formDataItems);
        $this->compileCsv($processedFormDataItems)->output($this->fileName);
    }

    public function compileAndSave(array $formDataItems, string $filePath, ExportDefinitionInterface $exportDefinition): void
    {
        $processedFormDataItems = array_map(static function (FormData $formData) use ($exportDefinition) {
            return $formData->getProcessedFormData($exportDefinition);
        }, $formDataItems);

        if (!file_put_contents($filePath, $this->compileCsv($processedFormDataItems)->toString())) {
            throw new \RuntimeException(sprintf('Unable to write form data export to file path "%s" - the file is not writable', $filePath), 1627881922);
        }
    }

    /**
     * @param iterable $formDataItems
     * @return Writer
     * @throws \League\Csv\CannotInsertRecord
     */
    protected function compileCsv(iterable $formDataItems): Writer
    {
        $csv = Writer::createFromString('');
        $headerSet = false;

        foreach ($formDataItems as $formDataItem) {

            if (!$headerSet) {
                $header = array_keys($formDataItem);
                $csv->insertOne($header);
                $headerSet = true;
            }

            $csv->insertOne($formDataItem);
        }

        return $csv;
    }
}
