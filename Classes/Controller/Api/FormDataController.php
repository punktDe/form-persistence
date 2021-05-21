<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Controller\Api;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Mvc\Controller\RestController;
use PunktDe\Form\Persistence\Domain\Model\FormData;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;

class FormDataController extends RestController
{
    /**
     * @var JsonView
     */
    protected $view = null;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'json' => JsonView::class
    ];

    /**
     * @var array
     */
    protected $supportedMediaTypes = [
        'application/json',
    ];

    /**
     * @Flow\Inject
     * @var FormDataRepository
     */
    protected $formDataRepository;

    public function listAction(): void
    {
        $this->view->setConfiguration([
            'value' => [
                '_descendAll' => [
                    '_exposeObjectIdentifier' => true,
                    '_only' => ['identifier', 'formIdentifier', 'hash']
                ]
            ]
        ]);
        $this->view->assign('value', array_map(static function ($entryGroup) {
            return current($entryGroup);
        }, $this->formDataRepository->findAllUniqueForms()));
    }

    public function showAction(): void
    {
        $this->view->setConfiguration([
            'value' => [
                '_exposeObjectIdentifier' => true,
                '_descend' => [
                    'formData' => [],
                    'processedFormData' => []
                ]
            ]
        ]);
        $formData = $this->formDataRepository->findByIdentifier($this->request->getArgument($this->resourceArgumentName));

        if ($formData instanceof FormData) {
            $this->view->assign('value', $formData->getProcessedFormData());
        }
    }
}
