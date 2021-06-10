<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Authorization\Privilege;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Security\Authorization\Privilege\AbstractPrivilege;
use Neos\Flow\Security\Authorization\Privilege\PrivilegeSubjectInterface;
use Neos\Flow\Security\Exception\InvalidPrivilegeTypeException;

class SitePrivilege extends AbstractPrivilege
{

    public function matchesSubject(PrivilegeSubjectInterface $subject)
    {

        if (!$subject instanceof SitePrivilegeTarget) {
            throw new InvalidPrivilegeTypeException(sprintf('Invalid subject type %s, only SitePrivilegeTarget supported.', get_class($subject)), 1613200124);
        }

        if ($this->getMatcher() === '*') {
            return true;
        }

        $permittedSites = explode(',', strtolower($this->getParsedMatcher()));
        return in_array(strtolower($subject->getSite()), $permittedSites, true);
    }
}
