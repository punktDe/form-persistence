<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Repository;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Persistence\Exception\InvalidQueryException;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;
use PunktDe\Form\Persistence\Authorization\Service\ContentDimensionAccessibilityService;
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

    /**
     * @Flow\Inject
     * @var ContentDimensionAccessibilityService
     */
    protected $dimensionAccesibilityService;

    protected $defaultOrderings = [
        'date' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @var string[]
     */
    protected $accessibleSites = [];

    /**
     * @var string[]
     */
    protected $accessibleDimensions = [];

    public function initializeObject(): void
    {
        $this->accessibleSites = $this->initializeAccessibleSites();
        $this->accessibleDimensions = $this->initializeAccessibleDimensions();
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
            ->orderBy('latestDate', QueryInterface::ORDER_DESCENDING)
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

    public function findLatestVersionOfForm(string $formIdentifier): ?FormData
    {
        $query = $this->createQuery();
        return $query->matching(
            $query->equals('formIdentifier', $formIdentifier)
        )->setOrderings(['date' => QueryInterface::ORDER_DESCENDING])
            ->execute()->getFirst();
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
        if (count($this->accessibleSites) > 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('form.siteName', $this->accessibleSites));
        } else {
            $queryBuilder->andWhere('2 = 1');
        }

        if (count($this->accessibleDimensions) > 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('form.dimensionsHash', $this->accessibleDimensions));
        } else {
            $queryBuilder->andWhere('2 = 1');
        }

        return $queryBuilder;
    }

    /**
     * @return string[]
     */
    public function getAccessibleSites(): ?array
    {
        return $this->accessibleSites;
    }


    public function deactivateSecurityChecks(): self
    {
        $this->accessibleSites = array_map(static function (FormData $formDataSample) {
            return $formDataSample->getSiteName();
        }, $this->findAllUnique('form.siteName'));

        $this->accessibleDimensions = array_map(static function (FormData $formDataSample) {
            return $formDataSample->getContentDimensions();
        }, $this->findAllUnique('form.dimensionsHash'));

        return $this;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function deleteAllOlderThan(DateTime $date): int
    {
        return $this->createQueryBuilder('formData')
            ->delete()
            ->where('formData.date < :date')
            ->setParameter(':date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function initializeAccessibleSites(): array
    {
        return array_filter(array_map(
            function (FormData $formDataSample) {
                return $this->siteAccesibilityService->isSiteAccessible($formDataSample->getSiteName()) ? $formDataSample->getSiteName() : null;
            },
            $this->findAllUnique('form.siteName')
        ));
    }

    protected function initializeAccessibleDimensions(): array
    {
        return array_filter(array_map(
            function (FormData $formDataSample) {
                return $this->dimensionAccesibilityService->isDimensionCombinationAccessible($formDataSample->getContentDimensions()) ? $formDataSample->getDimensionsHash() : null;
            },
            $this->findAllUnique('form.dimensionsHash')
        ));
    }

    protected function findAllUnique(string $groupField): array
    {
        $queryBuilder = parent::createQueryBuilder('form');
        $formDataGroupedBySite = $queryBuilder
            ->groupBy($groupField)
            ->getQuery()->execute();

        $formDataSample = [];

        /** @var FormData $formData */
        foreach ($formDataGroupedBySite as $formData) {
            $formDataSample[] = $formData;
        }

        return $formDataSample;
    }
}
