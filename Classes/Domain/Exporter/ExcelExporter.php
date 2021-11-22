<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PhpOffice\PhpSpreadsheet\Exception;
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
     * @throws WriterException|Exception
     */
    public function compileAndSend(iterable $formDataItems): void
    {
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

        $writer = IOFactory::createWriter($this->compileXLS($formDataItems), 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * @throws Exception
     * @throws WriterException
     */
    public function compileAndSave(iterable $formDataItems, string $filePath): void
    {
        $writer = IOFactory::createWriter($this->compileXLS($formDataItems), 'Xlsx');
        $writer->save($filePath);
    }

    /**
     * @throws Exception
     */
    protected function compileXLS(iterable $formDataItems) :Spreadsheet
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
        $spreadsheet->getActiveSheet()->fromArray($formDataItems);
        return $spreadsheet;
    }
}
