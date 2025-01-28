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
use Neos\Flow\Log\Utility\LogEnvironment;
use Psr\Log\LoggerInterface;
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
            $logger = Bootstrap::$staticObjectManager->get(LoggerInterface::class);
            $logger->error(sprintf('Error while saving the scheduled export definition for form data finisher with identifier %s. No form node could be determined', $node->getIdentifier()), LogEnvironment::fromMethodName(__METHOD__));
            return;
        }

        $formIdentifier = $form->getProperty('identifier');

        if ($node->isRemoved()) {
            $scheduledExportService->removeScheduledExportDefinitionIfExists($formIdentifier);
            return;
        }

        if (trim((string)$node->getProperty('scheduledExportRecipient')) === '' || trim((string)$node->getProperty('exportDefinition')) === '') {
            $scheduledExportService->removeScheduledExportDefinitionIfExists($formIdentifier);
            return;
        }

        $scheduledExportService->saveScheduledExportDefinition($formIdentifier, $node->getProperty('scheduledExportRecipient'), $node->getProperty('exportDefinition'));
    }
}
