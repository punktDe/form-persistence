<?php

declare(strict_types=1);

namespace PunktDe\Form\Persistence\CatchUpHook;

/*
*  (c) 2025 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
*  All rights reserved.
*/

use Neos\ContentRepository\Core\Projection\CatchUpHook\CatchUpHookFactoryDependencies;
use Neos\ContentRepository\Core\Projection\CatchUpHook\CatchUpHookFactoryInterface;
use Neos\ContentRepository\Core\Projection\CatchUpHook\CatchUpHookInterface;
use PunktDe\Form\Persistence\CatchUpHook\NodePublishedCatchUpHook;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentGraphReadModelInterface;
use PunktDe\Form\Persistence\Domain\ScheduledExport\ScheduledExportService;

/**
 * @implements CatchUpHookFactoryInterface<ContentGraphReadModelInterface>
 */
class NodePublishedCatchUpHookFactory implements CatchUpHookFactoryInterface
{

    public function __construct(
        private ScheduledExportService $scheduledExportService,
    ){
    }

    public function build(CatchUpHookFactoryDependencies $dependencies): CatchUpHookInterface
    {
        return new NodePublishedCatchUpHook(
            $dependencies->nodeTypeManager,
            $dependencies->projectionState,
            $this->scheduledExportService,
        );
    }
}
