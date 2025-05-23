<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Exporter;

/*
 *  (c) 2020-2025 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Neos\Utility\MediaTypes ;
use Neos\Flow\Annotations as Flow;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionInterface;
use PunktDe\Form\Persistence\Domain\Model\FormData;

class SpreadSheetExporter implements FormDataExporterInterface
{

    /**
     * @var string[]
     */
    protected array $options = [];

    protected string $fileName = 'FormData.xlsx';

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
     * @param ExportDefinitionInterface $exportDefinition
     * @return void
     * @throws WriterException|Exception
     */
    public function compileAndSend(array $formDataItems, ExportDefinitionInterface $exportDefinition): void
    {
        $this->sendHeader();
        $processedFormDataItems = array_map(static function (FormData $formData) use ($exportDefinition) {
            return $formData->getProcessedFormData($exportDefinition);
        }, $formDataItems);
        $writer = IOFactory::createWriter($this->compileXLS($processedFormDataItems), $this->options['writerType']);
        $writer->save('php://output');
        exit;
    }


    /**
     * @throws Exception
     * @throws WriterException
     */
    public function compileAndSave(array $formDataItems, string $filePath, ExportDefinitionInterface $exportDefinition): void
    {
        $processedFormDataItems = array_map(static function (FormData $formData) use ($exportDefinition) {
            return $formData->getProcessedFormData($exportDefinition);
        }, $formDataItems);
        $writer = IOFactory::createWriter($this->compileXLS($processedFormDataItems), $this->options['writerType']);
        $writer->save($filePath);
    }

    /**
     * @throws Exception
     */
    protected function compileXLS(iterable $formDataItems) :Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()
            ->setCreator($this->options['creator']??'')
            ->setTitle($this->options['title']??'');

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

    /**
     * @return void
     */
    public function sendHeader(): void
    {
        header("Pragma: public"); // required
        header("Expires: 0");
        header('Cache-Control: max-age=0');
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header('Content-Type: ' . MediaTypes::getMediaTypeFromFilename($this->fileName));
        header(
            sprintf(
                'Content-Disposition: attachment; filename="%s"',
                $this->fileName

            )
        );
        header("Content-Transfer-Encoding: binary");
    }
}
