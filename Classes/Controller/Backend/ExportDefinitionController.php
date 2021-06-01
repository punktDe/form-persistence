<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Controller\Backend;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Fusion\View\FusionView;

class ExportDefinitionController extends ActionController
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
     * @var UriBuilder
     */
    protected $uriBuilder;

    public function indexAction(): void
    {
        $request = $this->uriBuilder->getRequest();
        $format = $this->uriBuilder->getFormat();
        $this->uriBuilder->setRequest($this->controllerContext->getRequest()->getMainRequest());
        $this->uriBuilder->setFormat('json');

        $this->view->assignMultiple([
            'apiEndpoint' => [
                'formData' => $this->uriBuilder->uriFor('index', [], 'Api\\FormData', 'PunktDe.Form.Persistence'),
                'exportDefinition' => $this->uriBuilder->uriFor('index', [], 'Api\\ExportDefinition', 'PunktDe.Form.Persistence'),
            ]
        ]);

        $this->uriBuilder->setRequest($request);
        $this->uriBuilder->setFormat($format);
    }
}
