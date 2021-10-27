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

class ContentDimensionPrivilege extends AbstractPrivilege
{
    /**
     * @param PrivilegeSubjectInterface $subject
     * @return bool
     * @throws InvalidPrivilegeTypeException
     * @throws \JsonException
     */
    public function matchesSubject(PrivilegeSubjectInterface $subject)
    {


        if (!$subject instanceof ContentDimensionPrivilegeTarget) {
            throw new InvalidPrivilegeTypeException(sprintf('Invalid subject type %s, only ContentDimensionPrivilegeTarget supported.', get_class($subject)), 1635316933);
        }

        if ($this->getMatcher() === '*') {
            return true;
        }

        $matcherDimensionData = json_decode($this->matcher, true, 512, JSON_THROW_ON_ERROR);

        foreach ($subject->getContentDimensions() as $dimensionKey => $dimensionValues) {
            foreach ($dimensionValues as $dimensionValue) {
                if (isset($matcherDimensionData[$dimensionKey])) {
                    if ($matcherDimensionData[$dimensionKey] === '*') {
                        continue;
                    }
                    if (is_array($matcherDimensionData[$dimensionKey]) && in_array($dimensionValue, $matcherDimensionData[$dimensionKey], true)) {
                        continue;
                    }

                    return false;
                }
            }
        }

        return true;
    }
}
