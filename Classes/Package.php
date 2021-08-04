<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\ContentRepository\Domain\Service\PublishingService;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use PunktDe\Form\Persistence\SignalSlot\NodeSignalInterceptor;

class Package extends BasePackage
{
    /**
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(PublishingService::class, 'nodePublished', NodeSignalInterceptor::class, '::nodePublished');
    }
}
