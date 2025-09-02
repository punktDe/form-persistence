<?php

declare(strict_types=1);

namespace PunktDe\Form\Persistence\CatchUpHook;

/*
*  (c) 2025 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
*  All rights reserved.
*/

use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePointSet;
use Neos\ContentRepository\Core\EventStore\EventInterface;
use Neos\ContentRepository\Core\Feature\NodeCreation\Event\NodeAggregateWithNodeWasCreated;
use Neos\ContentRepository\Core\Feature\NodeModification\Event\NodePropertiesWereSet;
use Neos\ContentRepository\Core\Feature\NodeVariation\Event\NodeGeneralizationVariantWasCreated;
use Neos\ContentRepository\Core\Feature\NodeVariation\Event\NodePeerVariantWasCreated;
use Neos\ContentRepository\Core\Feature\NodeVariation\Event\NodeSpecializationVariantWasCreated;
use Neos\ContentRepository\Core\Feature\NodeRemoval\Event\NodeAggregateWasRemoved;
use Neos\ContentRepository\Core\Feature\SubtreeTagging\Dto\SubtreeTag;
use Neos\ContentRepository\Core\Feature\SubtreeTagging\Event\SubtreeWasTagged;
use Neos\ContentRepository\Core\Feature\SubtreeTagging\Event\SubtreeWasUntagged;
use Neos\ContentRepository\Core\NodeType\NodeTypeManager;
use Neos\ContentRepository\Core\Projection\CatchUpHook\CatchUpHookInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentGraphReadModelInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\VisibilityConstraints;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepository\Core\Subscription\SubscriptionStatus;
use Neos\EventStore\Model\EventEnvelope;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Neos\Domain\SubtreeTagging\NeosSubtreeTag;
use Psr\Log\LoggerInterface;
use Neos\Flow\Annotations as Flow;
use PunktDe\Form\Persistence\Domain\ScheduledExport\ScheduledExportService;
use PunktDe\Form\Persistence\FormPersistenceNodeTypeInterface;
use Neos\Eel\FlowQuery\FlowQuery;

final class NodePublishedCatchUpHook implements CatchUpHookInterface
{

    #[Flow\Inject]
    protected LoggerInterface $logger;

    public function __construct(
        private readonly NodeTypeManager $nodeTypeManager,
        private readonly ContentGraphReadModelInterface $contentGraphReadModel,
        private readonly ScheduledExportService $scheduledExportService,
    ) {
    }

    public function onBeforeCatchUp(SubscriptionStatus $subscriptionStatus): void
    {
    }

    public function onBeforeEvent(EventInterface $eventInstance, EventEnvelope $eventEnvelope): void
    {
        match ($eventInstance::class) {
            NodeAggregateWasRemoved::class => $this->removeScheduledExportDefinition($eventInstance->getWorkspaceName(), $eventInstance->nodeAggregateId, $eventInstance->affectedCoveredDimensionSpacePoints),
            default => null
        };
    }

    public function onAfterEvent(EventInterface $eventInstance, EventEnvelope $eventEnvelope): void
    {
        match ($eventInstance::class) {
            NodeAggregateWithNodeWasCreated::class => $this->saveScheduledExportDefinition($eventInstance->getWorkspaceName(), $eventInstance->nodeAggregateId, $eventInstance->originDimensionSpacePoint->toDimensionSpacePoint()),
            NodePeerVariantWasCreated::class => $this->saveScheduledExportDefinition($eventInstance->getWorkspaceName(), $eventInstance->nodeAggregateId, $eventInstance->originDimensionSpacePoint->toDimensionSpacePoint()),
            NodeGeneralizationVariantWasCreated::class => $this->saveScheduledExportDefinition($eventInstance->getWorkspaceName(), $eventInstance->nodeAggregateId, $eventInstance->originDimensionSpacePoint->toDimensionSpacePoint()),
            NodeSpecializationVariantWasCreated::class => $this->saveScheduledExportDefinition($eventInstance->getWorkspaceName(), $eventInstance->nodeAggregateId, $eventInstance->originDimensionSpacePoint->toDimensionSpacePoint()),
            NodePropertiesWereSet::class => $this->saveScheduledExportDefinition($eventInstance->getWorkspaceName(), $eventInstance->nodeAggregateId, $eventInstance->originDimensionSpacePoint->toDimensionSpacePoint()),
            SubtreeWasTagged::class => $this->handleSubtreeTags($eventInstance->getWorkspaceName(), $eventInstance->nodeAggregateId, $eventInstance->tag, $eventInstance->affectedDimensionSpacePoints),
            SubtreeWasUntagged::class => $this->updateNodesInDimensionSpacePoints($eventInstance->getWorkspaceName(), $eventInstance->nodeAggregateId, $eventInstance->affectedDimensionSpacePoints),
            default => null
        };
    }

    public function onAfterCatchUp(): void
    {
    }

    public function onAfterBatchCompleted(): void
    {
    }

    private function saveScheduledExportDefinition(WorkspaceName $workspaceName, NodeAggregateId $nodeAggregateId, DimensionSpacePoint $dimensionSpacePoint): void
    {
        if (!$workspaceName->isLive()) {
            return;
        }

        $contentGraph = $this->contentGraphReadModel->getContentGraph($workspaceName);
        $node = $contentGraph->getSubgraph($dimensionSpacePoint, VisibilityConstraints::createEmpty())->findNodeById($nodeAggregateId);

        if ($node === null) {
            // Node not found, nothing to do here.
            return;
        }

        if (!$this->nodeTypeManager->getNodeType($node->nodeTypeName)->isOfType(FormPersistenceNodeTypeInterface::NODE_TYPE_SAVE_FORM_DATA_FINISHER)) {
            return;
        }


        if (trim((string)$node->getProperty('scheduledExportRecipient')) === '' || trim((string)$node->getProperty('exportDefinition')) === '') {
            // Needed information for schedule export is missing.
            return;
        }


        $form = (new FlowQuery([$node]))->closest('[instanceof Neos.Form.Builder:NodeBasedForm]')->get(0);

        if (!$form instanceof Node) {
            $this->logger->error(sprintf('Error while saving the scheduled export definition for form data finisher with identifier %s. No form node could be determined', $node->aggregateId->value), LogEnvironment::fromMethodName(__METHOD__));
            return;
        }

        $formIdentifier = $form->getProperty('identifier');

        if ($formIdentifier === '' || $formIdentifier === null) {
            $this->logger->error(sprintf('Error while saving the scheduled export definition for form data finisher with identifier %s. No formidentifier could be determined', $node->aggregateId->value), LogEnvironment::fromMethodName(__METHOD__));
            return;
        }

        $this->scheduledExportService->saveScheduledExportDefinition($formIdentifier, $node->getProperty('scheduledExportRecipient'), $node->getProperty('exportDefinition'));
    }

    private function removeScheduledExportDefinition(WorkspaceName $workspaceName, NodeAggregateId $nodeAggregateId, DimensionSpacePointSet $dimensionSpacePoints): void
    {
        if (!$workspaceName->isLive()) {
            return;
        }

        $contentGraph = $this->contentGraphReadModel->getContentGraph($workspaceName);

        foreach ($dimensionSpacePoints as $dimensionSpacePoint) {
            $node = $contentGraph->getSubgraph($dimensionSpacePoint, VisibilityConstraints::createEmpty())->findNodeById($nodeAggregateId);

            if ($node === null) {
                // Node not found, nothing to do here.
                return;
            }

            if (!$this->nodeTypeManager->getNodeType($node->nodeTypeName)->isOfType(FormPersistenceNodeTypeInterface::NODE_TYPE_SAVE_FORM_DATA_FINISHER)) {
                return;
            }

            $form = (new FlowQuery([$node]))->closest('[instanceof Neos.Form.Builder:NodeBasedForm]')->get(0);

            if (!$form instanceof Node) {
                $this->logger->error(sprintf('Error while removing the scheduled export definition for form data finisher with identifier %s. No form node could be determined', $node->aggregateId->value), LogEnvironment::fromMethodName(__METHOD__));
                return;
            }

            $formIdentifier = $form->getProperty('identifier');
            $this->scheduledExportService->removeScheduledExportDefinitionIfExists($formIdentifier);
        }
    }

    private function handleSubtreeTags(WorkspaceName $workspaceName, NodeAggregateId $nodeAggregateId, SubtreeTag $tag, DimensionSpacePointSet $affectedDimensionSpacePoints): void
    {
        if ($tag === NeosSubtreeTag::removed()) {
            $this->removeScheduledExportDefinition($workspaceName, $nodeAggregateId, $affectedDimensionSpacePoints);
            return;
        }
    }

    private function updateNodesInDimensionSpacePoints(WorkspaceName $workspaceName, NodeAggregateId $nodeAggregateId, DimensionSpacePointSet $dimensionSpacePointSet): void
    {
        foreach ($dimensionSpacePointSet as $dimensionSpacePoint) {
            $this->saveScheduledExportDefinition($workspaceName, $nodeAggregateId, $dimensionSpacePoint);
        }
    }
}
