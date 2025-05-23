<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Authorization\Service;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use PunktDe\Form\Persistence\Authorization\Privilege\ContentDimensionPrivilege;
use PunktDe\Form\Persistence\Authorization\Privilege\ContentDimensionPrivilegeTarget;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Security\Context as SecurityContext;

/**
 * @Flow\Scope("singleton")
 */
class ContentDimensionAccessibilityService
{
    #[Flow\Inject]
    protected PrivilegeManagerInterface $privilegeManager;

    #[Flow\Inject]
    protected SecurityContext $securityContext;

    public function isDimensionCombinationAccessible(array $dimensionCombination): bool
    {
        if (!$this->securityContext->canBeInitialized()) {
            return false;
        }

        return $this->privilegeManager->isGranted(ContentDimensionPrivilege::class, new ContentDimensionPrivilegeTarget($dimensionCombination));
    }
}
