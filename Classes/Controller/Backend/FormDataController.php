<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Controller\Backend;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Exception;
use League\Csv\CannotInsertRecord;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\NoSuchArgumentException;
use Neos\Fusion\View\FusionView;
use PunktDe\Form\Persistence\Domain\Exporter\CsvExporter;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;

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
     * @var CsvExporter
     */
    protected $csvExporter;

    public function indexAction(): void
    {
        $formTypes = $this->formDataRepository->findAllUniqueForms();
        $this->view->assign('formTypes', $formTypes);
    }

    /**
     * @throws CannotInsertRecord
     * @throws NoSuchArgumentException
     * @throws Exception
     */
    public function downloadAction(): void
    {
        $formIdentifier = $this->request->getParentRequest()->getArgument('formIdentifier');
        $hash = $this->request->getParentRequest()->getArgument('hash');

        /** @var FormData[] $formDataItems */
        $formDataItems = $this->formDataRepository->findByFormIdentifierAndHash($formIdentifier, $hash)->toArray();

        $fileName = sprintf('Form-Export-%s-%s.csv', $formIdentifier, (new \DateTime())->format('Y-m-d-H-i-s'));

        $this->csvExporter->compileAndSend($formDataItems, $fileName);

        die();
    }
}
