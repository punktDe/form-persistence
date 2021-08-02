<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use League\Csv\Writer;

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

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param iterable $formDataItems
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function compileAndSend(iterable $formDataItems): void
    {
        $this->compileCsv($formDataItems)->output($this->fileName);
    }

    public function compileToTemporaryFile(iterable $formDataItems)
    {
        $file = tmpfile();

        $this->compileCsv($formDataItems)->toString();
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
