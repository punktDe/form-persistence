<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Controller\Backend;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Fusion\View\FusionView;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;

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
     * @var FormDataRepository
     */
    protected $formDataRepository;

    public function indexAction(): void
    {

    }

    public function preSelectFormFieldsAction(): void
    {

    }

    public function newAction(): void
    {

    }

    public function createAction(): void
    {

    }

    public function editAction(): void
    {

    }

    public function updateAction(): void
    {

    }
}
