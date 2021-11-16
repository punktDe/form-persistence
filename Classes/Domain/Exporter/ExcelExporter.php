<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use phpoffice\Excel\CannotInsertRecord;
use Wegmeister\DatabaseStorage\Domain\Model\DatabaseStorage;
use Wegmeister\DatabaseStorage\Domain\Repository\DatabaseStorageRepository;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;


class ExcelExporter implements FormDataExporterInterface
{

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $fileName = 'FormData.xlsx';





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
     * @throws CannotInsertRecord
     */
    public function compileAndSend(iterable $formDataItems): void
    {
        $this->compileXLS($formDataItems)->output($this->fileName);
    }

    public function compileAndSave(iterable $formDataItems, string $filePath): void
    {
        if (!file_put_contents($filePath, $this->compileXLS($formDataItems)->toString())) {
            throw new \RuntimeException(sprintf('Unable to write form data export to file path "%s" - the file is not writable', $filePath), 1627881922);
        }
    }

    protected function compileXLS(iterable $formDataItems)
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()
            ->setCreator('creator')
            ->setTitle('title')
            ->setSubject('subject');
        $headerColumns = array_keys($formDataItems[0]);
        array_unshift($formDataItems ,$headerColumns );

        //Format Headline
        $prefixIndex = 64;
        $prefixKey = '';
        for ($i = 0; $i < count($headerColumns); $i++) {
            $index = $i % 26;
            $columnStyle = $spreadsheet->getActiveSheet()->getStyle($prefixKey . chr(65 + $index) . '1');
            $columnStyle->getFont()->setBold(true);
            $columnStyle->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            if ($index + 1 > 25) {
                $prefixIndex++;
                $prefixKey = chr($prefixIndex);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setTitle('test1');
        $spreadsheet->getActiveSheet()->fromArray($formDataItems);

        header("Pragma: public"); // required
        header("Expires: 0");
        header('Cache-Control: max-age=0');
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header(
            sprintf(
                'Content-Disposition: attachment; filename="%s"',
                $this->fileName

            )
        );
        header("Content-Transfer-Encoding: binary");

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;

    }
}
