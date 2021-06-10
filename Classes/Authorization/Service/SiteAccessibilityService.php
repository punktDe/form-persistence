<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Authorization\Service;

use PunktDe\Form\Persistence\Authorization\Privilege\SitePrivilege;
use PunktDe\Form\Persistence\Authorization\Privilege\SitePrivilegeTarget;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Security\Context as SecurityContext;

/**
 * @Flow\Scope("singleton")
 */
class SiteAccessibilityService
{

    /**
     * @Flow\Inject
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    public function isSiteAccessible(string $site): bool
    {
        if (!$this->securityContext->canBeInitialized()) {
            return false;
        }

        return $this->privilegeManager->isGranted(SitePrivilege::class, new SitePrivilegeTarget($site));
    }
}
