<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\SignalSlot;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Core\Bootstrap;
use PunktDe\Form\Persistence\Domain\ScheduledExport\ScheduledExportService;
use PunktDe\Form\Persistence\FormPersistenceNodeTypeInterface;

class NodeSignalInterceptor
{

    /**
     * @param NodeInterface $node
     * @param Workspace $targetWorkspace
     * @throws \Neos\Eel\Exception
     */
    public static function nodePublished(NodeInterface $node, Workspace $targetWorkspace): void
    {

        if (!$targetWorkspace->isPublicWorkspace()) {
            return;
        }

        if (!$node->getNodeType()->isOfType(FormPersistenceNodeTypeInterface::NODE_TYPE_SAVE_FORM_DATA_FINISHER)) {
            return;
        }

        $scheduledExportService = Bootstrap::$staticObjectManager->get(ScheduledExportService::class);
        $form = (new FlowQuery([$node]))->closest('[instanceof Neos.Form.Builder:NodeBasedForm]')->get(0);

        if (!$form instanceof NodeInterface) {
            throw new \Exception('Error while saving the scheduled export definition. No form node could be determined', 1627803571);
        }

        $formIdentifier = $form->getProperty('identifier');

        if (trim($node->getProperty('scheduledExportRecipient')) === '' || trim($node->getProperty('exportDefinition')) === '') {
            $scheduledExportService->removeScheduledExportDefinitionIfExists($formIdentifier);
            return;
        }

        $scheduledExportService->saveScheduledExportDefinition($formIdentifier, $node->getProperty('scheduledExportRecipient'), $node->getProperty('exportDefinition'));
    }
}
