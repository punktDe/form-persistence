<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Controller\Backend;

/*
 *  (c) 2020-2025 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Doctrine\ORM\ORMException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\ForwardException;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\ObjectManagement\Exception\CannotBuildObjectException;
use Neos\Flow\ObjectManagement\Exception\UnknownObjectException;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Persistence\Exception\InvalidQueryException;
use Neos\Fusion\View\FusionView;
use PunktDe\Form\Persistence\Domain\ExportDefinition\ExportDefinitionProvider;
use PunktDe\Form\Persistence\Domain\Exporter\ExporterFactory;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;
use PunktDe\Form\Persistence\Domain\Repository\ScheduledExportRepository;
use PunktDe\Form\Persistence\Exception\ConfigurationException;
use PunktDe\Form\Persistence\Service\TemplateStringService;

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

    #[Flow\Inject]
    protected FormDataRepository $formDataRepository;

    #[Flow\Inject]
    protected ExportDefinitionProvider $exportDefinitionProvider;

    #[Flow\Inject]
    protected ExporterFactory $exporterFactory;

    #[Flow\Inject]
    protected ScheduledExportRepository $scheduledExportRepository;

    public function indexAction(): void
    {
        $formTypes = array_map(function ($formData) {
            /** @var FormData $formDataObject */
            $formDataObject = $formData[0];
            $formData['scheduledExport'] = $this->scheduledExportRepository->findOneByFormIdentifier($formDataObject->getFormIdentifier());
            return $formData;
        }, (array)$this->formDataRepository->findAllUniqueForms());


        $this->view->assign('formTypes', $formTypes);
    }

    /**
     * @param string $formIdentifier
     * @param string $hash
     * @param string $siteName
     * @param string $dimensionsHash
     * @param string $exportDefinitionIdentifier
     * @throws InvalidConfigurationTypeException
     * @throws CannotBuildObjectException
     * @throws UnknownObjectException
     * @throws ConfigurationException
     * @throws InvalidQueryException
     */
    public function downloadAction(string $formIdentifier, string $hash, string $siteName, string $dimensionsHash, string $exportDefinitionIdentifier): void
    {
        /** @var FormData[] $formDataItems */

        $exportDefinition = $this->exportDefinitionProvider->getExportDefinitionByIdentifier($exportDefinitionIdentifier);
        $exporter = $this->exporterFactory->makeExporterByExportDefinition($exportDefinition);

        $fileName = TemplateStringService::processTemplate($exportDefinition->getFileNamePattern(), $formIdentifier, $hash, $exportDefinition);

        $formDataItems =  $this->formDataRepository->findByFormProperties($formIdentifier, $hash, $siteName, $dimensionsHash)->toArray();

        $exporter->setFileName($fileName)->compileAndSend($formDataItems, $exportDefinition);
        die();
    }

    /**
     * @param FormData $formDataEntry
     * @throws ForwardException
     * @throws InvalidQueryException
     */
    public function previewAction(FormData $formDataEntry): void
    {
        $formDataEntries = $this->formDataRepository->findByFormProperties($formDataEntry->getFormIdentifier(), $formDataEntry->getHash(), $formDataEntry->getSiteName(), $formDataEntry->getDimensionsHash());
        $scheduledExport = $this->scheduledExportRepository->findOneByFormIdentifier($formDataEntry->getFormIdentifier());

        if ($formDataEntries->count() === 0) {
            $this->forward('index');
        }

        /** @var FormData $firstFormDataEntry */
        $firstFormDataEntry = $formDataEntries->getFirst();

        $this->view->assignMultiple([
            'formIdentifier' => $firstFormDataEntry->getFormIdentifier(),
            'headerFields' => $firstFormDataEntry->getProcessedFieldNames(),
            'scheduledExport' => $scheduledExport,
            'formDataEntries' => $formDataEntries,
        ]);
    }

    /**
     * @param FormData $formDataEntry
     * @throws StopActionException
     */
    public function deleteAction(FormData $formDataEntry): void
    {
        $this->formDataRepository->removeByFormProperties($formDataEntry->getFormIdentifier(), $formDataEntry->getHash(), $formDataEntry->getSitename(), $formDataEntry->getDimensionsHash());
        $this->redirect('index');
    }

    /**
     * @param FormData $formDataEntry
     * @throws StopActionException
     * @throws ORMException
     * @throws IllegalObjectTypeException
     */
    public function deleteSingleFormDataEntryAction(FormData $formDataEntry): void
    {
        $this->formDataRepository->remove($formDataEntry);
        $this->redirect('index');
    }
}
