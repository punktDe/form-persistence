<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Controller\Api;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Doctrine\ORM\ORMException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Mvc\Controller\RestController;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Persistence\Exception\UnknownObjectException;
use Neos\Utility\ObjectAccess;
use PunktDe\Form\Persistence\Domain\Model\ExportDefinition;
use PunktDe\Form\Persistence\Domain\Repository\ExportDefinitionRepository;

class ExportDefinitionController extends RestController
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
     * @var ExportDefinitionRepository
     */
    protected $exportDefinitionRepository;

    protected function initializeCreateAction()
    {
    }

    public function listAction(): void
    {
        $this->view->setConfiguration([
            'value' => [
                '_descendAll' => [
                    '_exposeObjectIdentifier' => true,
                    '_only' => ['label', 'exporter'],
                ]
            ]
        ]);
        $this->view->assign('value', $this->exportDefinitionRepository->findAll());
    }

    public function showAction(): void
    {
        $this->view->setConfiguration([
            'value' => [
                '_exposeObjectIdentifier' => true,
                '_descend' => [
                    'definition' => []
                ],
                '_exclude' => ['suitableFor'],
            ]
        ]);
        $this->view->assign('value', $this->exportDefinitionRepository->findByIdentifier($this->request->getArgument($this->resourceArgumentName)));
    }

    public function createAction(): void
    {
        $exportDefinition = new ExportDefinition();
        $this->setDataFromRequestBody($exportDefinition);
        $this->exportDefinitionRepository->add($exportDefinition);
    }

    /**
     * @param ExportDefinition $resource
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(ExportDefinition $resource): void
    {
        $this->setDataFromRequestBody($resource);
        $this->exportDefinitionRepository->update($resource);
    }

    /**
     * @param ExportDefinition $resource
     * @throws ORMException
     * @throws IllegalObjectTypeException
     */
    public function deleteAction(ExportDefinition $resource): void
    {
        if ($resource instanceof ExportDefinition) {
            $this->exportDefinitionRepository->remove($resource);
        }
    }

    private function setDataFromRequestBody(ExportDefinition $exportDefinition): void
    {
        $data = $this->request->getHttpRequest()->getParsedBody();

        $propertyNames = ObjectAccess::getSettablePropertyNames($exportDefinition);
        foreach ($propertyNames as $propertyName) {
            if (!isset($data[$propertyName])) {
                continue;
            }

            $setterName = 'set' . ucfirst($propertyName);
            if (!method_exists($exportDefinition, $setterName)) {
                continue;
            }

            $exportDefinition->$setterName($data[$propertyName]);
        }
    }
}
