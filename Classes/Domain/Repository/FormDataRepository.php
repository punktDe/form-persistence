<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Repository;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Persistence\Exception\InvalidQueryException;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;
use PunktDe\Form\Persistence\Authorization\Service\SiteAccessibilityService;
use PunktDe\Form\Persistence\Domain\Model\FormData;

/**
 * @Flow\Scope("singleton")
 */
class FormDataRepository extends Repository
{

    /**
     * @Flow\Inject
     * @var SiteAccessibilityService
     */
    protected $siteAccesibilityService;

    protected $defaultOrderings = [
        'date' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @var string[]
     */
    protected $accessibleSites = null;

    public function initializeObject(): void
    {
        $this->accessibleSites = $this->getAccessibleSites();
    }

    /**
     * @return iterable
     */
    public function findAllUniqueForms(): iterable
    {
        $queryBuilder = $this->createQueryBuilder('form');
        return $queryBuilder
            ->groupBy('form.formIdentifier')
            ->addGroupBy('form.hash')
            ->addSelect('count(form) AS entryCount')
            ->addSelect('MAX(form.date) as latestDate')
            ->orderBy('latestDate', 'DESC')
            ->getQuery()->execute();
    }

    /**
     * @param string $formIdentifier
     * @param string $hash
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByFormIdentifierAndHash(string $formIdentifier, string $hash): QueryResultInterface
    {
        $query = $this->createQuery();

        return $query->matching(
            $query->logicalAnd(
                $query->equals('formIdentifier', $formIdentifier),
                $query->equals('hash', $hash),
                $query->in('siteName', $this->accessibleSites)
            )
        )->execute();
    }

    public function removeByFormIdentifierAndHash(string $formIdentifier, string $hash): void
    {
        foreach ($this->findByFormIdentifierAndHash($formIdentifier, $hash) as $formData) {
            $this->remove($formData);
        }
    }

    /**
     * @param string $formIdentifier
     * @param string $data
     * @return FormData|null
     * @throws InvalidQueryException
     */
    public function findFormDataWithIdentifierContainingData(string $formIdentifier, string $data): ?FormData
    {
        $query = $this->createQuery();

        $result = $query->matching(
            $query->logicalAnd(
                $query->equals('formIdentifier', $formIdentifier),
                $query->like('formData', '%' . $data . '%'),
                $query->in('siteName', $this->accessibleSites)
            )
        )->execute()->getFirst();

        /** @var FormData $result */
        return $result;
    }

    public function createQueryBuilder($alias, $indexBy = null)
    {
        $queryBuilder = parent::createQueryBuilder($alias, $indexBy);
        return $queryBuilder->andWhere($queryBuilder->expr()->in('form.siteName', $this->accessibleSites));
    }

    public function getAccessibleSites(): array
    {
        return $this->accessibleSites ?? array_filter(
                $this->findAllUniqueSiteNames(),
                function (string $site) {
                    return $this->siteAccesibilityService->isSiteAccessible($site);
                }
            );
    }

    protected function findAllUniqueSiteNames(): array
    {
        $queryBuilder = parent::createQueryBuilder('form');
        $formDataGroupedBySite = $queryBuilder
            ->groupBy('form.siteName')
            ->getQuery()->execute();

        $sites = [];

        /** @var FormData $formData */
        foreach ($formDataGroupedBySite as $formData) {
            $sites[] = $formData->getSiteName();
        }

        return $sites;
    }
}
