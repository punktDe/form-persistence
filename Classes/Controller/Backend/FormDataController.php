<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Controller\Backend;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\ObjectManagement\Exception\CannotBuildObjectException;
use Neos\Flow\ObjectManagement\Exception\UnknownObjectException;
use Neos\Fusion\View\FusionView;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionProvider;
use PunktDe\Form\Persistence\Domain\Exporter\ExporterFactory;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Processors\FieldKeyMappingProcessor;
use PunktDe\Form\Persistence\Domain\Processors\ValueFormattingProcessor;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;
use PunktDe\Form\Persistence\Exception\ConfigurationException;

class FormDataController extends ActionController
{
    /**
     * @var FusionView
     */
    protected $view;

    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @Flow\Inject
     * @var FormDataRepository
     */
    protected $formDataRepository;

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
     * @var FieldKeyMappingProcessor
     */
    protected $fieldKeyMappingProcessor;

    /**
     * @Flow\Inject
     * @var ValueFormattingProcessor
     */
    protected $valueFormattingProcessor;

    public function indexAction(): void
    {
        $formTypes = $this->formDataRepository->findAllUniqueForms();
        $this->view->assign('formTypes', $formTypes);
    }

    /**
     * @param string $formIdentifier
     * @param string $hash
     * @param string $exportDefinitionIdentifier
     * @throws InvalidConfigurationTypeException
     * @throws CannotBuildObjectException
     * @throws UnknownObjectException
     * @throws ConfigurationException
     */
    public function downloadAction(string $formIdentifier, string $hash, string $exportDefinitionIdentifier): void
    {
        /** @var FormData[] $formDataItems */

        $exportDefinition = $this->exportDefinitionProvider->getExportDefinitionByIdentifier($exportDefinitionIdentifier);
        $exporter = $this->exporterFactory->makeExporterByExportDefinition($exportDefinition);

        $fileName = str_replace(
            ['formIdentifier', 'currentDate', 'exportDefinitionIdentifier', 'formVersionHash'],
            [$formIdentifier, date('Y-m-d_his'), $exportDefinition, $hash],
            $exportDefinition->getFileNamePattern()
        );

        $formDataItems = array_map(function (FormData $formData) use ($exportDefinition) {
            return $this->valueFormattingProcessor->convertFormData(
                $this->fieldKeyMappingProcessor->convertFormData($formData->getFormData(), $exportDefinition->getDefinition()),
                $exportDefinition->getDefinition()
            );
        }, $this->formDataRepository->findByFormIdentifierAndHash($formIdentifier, $hash)->toArray());

        $exporter->compileAndSend($formDataItems);
        $exporter->setFileName($fileName);

        die();
    }
}
