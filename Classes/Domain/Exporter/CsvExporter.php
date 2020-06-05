<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use League\Csv\Writer;
use Neos\Flow\Http\Component\SetHeaderComponent;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\ResourceManagement\PersistentResource;

class CsvExporter implements FormDataExporterInterface
{

    /**
     * @param iterable $formDataItems
     * @param string $fileName
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function compileAndSend(iterable $formDataItems, string $fileName): void
    {
        $csv = Writer::createFromString('');
        $headerSet = false;

        foreach ($formDataItems as $key => $formDataItem) {
            $dataRow = [];
            foreach ($formDataItem->getFormData() as $fieldIdentifier => $fieldValue) {
                if ($fieldValue instanceof PersistentResource) {
                    $dataRow[$fieldIdentifier] = $fieldValue->getFilename();
                    continue;
                }

                if (is_array($fieldValue) && array_key_exists('date', $fieldValue)) {
                    $dataRow[] = (new \DateTime($fieldValue['date']))
                        ->setTimezone(new \DateTimeZone($fieldValue['timezone']))
                        ->format('d.m.Y');
                    continue;
                }

                if (is_array($fieldValue)) {
                    continue;
                }

                $dataRow[$fieldIdentifier] = $fieldValue;
            }

            if (!$headerSet) {
                $header = array_keys($dataRow);
                $csv->insertOne($header);
                $headerSet = true;
            }

            $csv->insertOne($dataRow);
        }

        $csv->output($fileName);
    }
}
