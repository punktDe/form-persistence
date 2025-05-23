<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Authorization\Privilege;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Security\Authorization\Privilege\PrivilegeSubjectInterface;

class SitePrivilegeTarget implements PrivilegeSubjectInterface
{
    protected string $site;

    public function __construct(string $site)
    {
        $this->site = $site;
    }

    public function getSite(): string
    {
        return $this->site;
    }
}
