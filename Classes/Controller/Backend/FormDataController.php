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
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
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

        $formDataItems = array_map(static function (FormData $formData) use ($exportDefinition) {
            return $formData->getProcessedFormData($exportDefinition);
        }, $this->formDataRepository->findByFormIdentifierAndHash($formIdentifier, $hash)->toArray());

        $exporter->compileAndSend($formDataItems);
        $exporter->setFileName($fileName);

        die();
    }

    public function previewAction(FormData $formDataEntry): void
    {
        $formDataEntries = $this->formDataRepository->findByFormIdentifierAndHash($formDataEntry->getFormIdentifier(), $formDataEntry->getHash());

        if ($formDataEntries->count() === 0) {
            $this->forward('index');
        }

        $formDataValues = array_map(static function (FormData $formData) {
            return $formData->getProcessedFormData();
        }, $formDataEntries->toArray());

        /** @var FormData $firstFormDataEntry */
        $firstFormDataEntry = $formDataEntries->getFirst();

        $this->view->assignMultiple([
            'formIdentifier' => $firstFormDataEntry->getFormIdentifier(),
            'headerFields' => array_keys(current($formDataValues)),
            'formDataValues' => $formDataValues,
        ]);
    }

    /**
     * @param FormData $formDataEntry
     * @throws \Doctrine\ORM\ORMException
     * @throws IllegalObjectTypeException
     */
    public function deleteAction(FormData $formDataEntry): void
    {
        $this->formDataRepository->removeByFormIdentifierAndHash($formDataEntry->getFormIdentifier(), $formDataEntry->getHash());
        $this->redirect('index');
    }
}
