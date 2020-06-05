<?php

declare(strict_types=1);

namespace PunktDe\Form\Persistence\Controller\Backend;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use DateTime;
use DateTimeZone;
use Exception;
use GuzzleHttp\Psr7\Stream;
use League\Csv\CannotInsertRecord;
use League\Csv\Writer;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Component\SetHeaderComponent;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\NoSuchArgumentException;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Fusion\View\FusionView;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;
use function GuzzleHttp\Psr7\stream_for;

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

    public function indexAction(): void
    {
        $formTypes = $this->formDataRepository->findAllUniqueForms() ?? [];
        $this->view->assign('formTypes', $formTypes);
    }

    /**
     * @throws CannotInsertRecord
     * @throws NoSuchArgumentException
     * @throws Exception
     */
    public function downloadAction()
    {
        $formIdentifier = $this->request->getParentRequest()->getArgument('formIdentifier');
        $hash = $this->request->getParentRequest()->getArgument('hash');

        /** @var FormData[] $formDataItems */
        $formDataItems = $this->formDataRepository->findByFormIdentifierAndHash($formIdentifier, $hash)->toArray();

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
                    $dataRow[] = (new DateTime($fieldValue['date']))
                        ->setTimezone(new DateTimeZone($fieldValue['timezone']))
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

        $this->response->setContentType('application/text');
        $this->response->setComponentParameter(
            SetHeaderComponent::class,
            'Content-Disposition',
            'attachment; filename="Form-Export-' . $formIdentifier . (new DateTime())->format('Y-m-d-H-i-s') . '.csv"'
        );
        $csv->output('Form-Export-' . $formIdentifier . '-' . (new DateTime())->format('Y-m-d-H-i-s') . '.csv');
        die();
    }
}
