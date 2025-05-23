<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Authorization\Privilege;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Security\Authorization\Privilege\PrivilegeSubjectInterface;

class ContentDimensionPrivilegeTarget implements PrivilegeSubjectInterface
{
    /**
     * @var string[]
     */
    protected $contentDimensions = [];

    /**
     * @param string[] $contentDimensions
     */
    public function __construct(array $contentDimensions)
    {
        $this->contentDimensions = $contentDimensions;
    }

    /**
     * @return string[]
     */
    public function getContentDimensions(): array
    {
        return $this->contentDimensions;
    }
}
