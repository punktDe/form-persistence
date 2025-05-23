<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\FormDataCleanup;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Doctrine\ORM\ORMException;
use Neos\Flow\Annotations as Flow;
use PunktDe\Form\Persistence\Domain\Repository\FormDataRepository;

class FormDataCleanupService
{
    #[Flow\Inject]
    protected FormDataRepository $formDataRepository;

    #[Flow\InjectConfiguration(path: 'formDataCleanup.retentionPeriod', package: 'PunktDe.Form.Persistence')]
    protected string $dateInterval;

    /**
     * @throws ORMException
     * @throws \Exception
     */
    public function cleanupOldFormData(): int
    {
        $date = (new \DateTime())->sub(new \DateInterval($this->dateInterval));
        return $this->formDataRepository->deactivateSecurityChecks()->deleteAllOlderThan($date);
    }
}
