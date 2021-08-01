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
        $csv = Writer::createFromString('');
        $headerSet = false;

        foreach ($formDataItems as $key => $formDataItem) {

            if (!$headerSet) {
                $header = array_keys($formDataItem);
                $csv->insertOne($header);
                $headerSet = true;
            }

            $csv->insertOne($formDataItem);
        }

        $csv->output($this->fileName);
    }
}
