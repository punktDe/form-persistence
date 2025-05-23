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
    #[Flow\Inject]
    protected SiteAccessibilityService $siteAccesibilityService;

    #[Flow\Inject]
    protected ContentDimensionAccessibilityService $dimensionAccesibilityService;

    /**
     * @var string[]
     */
    protected $defaultOrderings = [
        'date' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @var string[]
     */
    protected array $accessibleSites = [];

    /**
     * @var string[]
     */
    protected array $accessibleDimensions = [];

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
            ->groupBy('form.siteName')
            ->addgroupBy('form.formIdentifier')
            ->addGroupBy('form.hash')
            ->addGroupBy('form.dimensionsHash')
            ->addSelect('count(form) AS entryCount')
            ->addSelect('MAX(form.date) as latestDate')
            ->orderBy('latestDate', QueryInterface::ORDER_DESCENDING)
            ->getQuery()->execute();
    }

    /**
     * @param string $formIdentifier
     * @param string $hash
     * @param string $siteName
     * @param string $dimensionsHash
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByFormProperties(string $formIdentifier, string $hash, string $siteName, string $dimensionsHash): QueryResultInterface
    {
        $query = $this->createQuery();

        return $query->matching(
            $query->logicalAnd(
                $query->equals('formIdentifier', $formIdentifier),
                $query->equals('hash', $hash),
                $query->equals('siteName', $siteName),
                $query->equals('dimensionsHash', $dimensionsHash),
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

    public function removeByFormProperties(string $formIdentifier, string $hash, string $siteName, string $dimensionsHash): void
    {
        foreach ($this->findByFormProperties($formIdentifier, $hash, $siteName, $dimensionsHash) as $formData) {
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
        return parent::createQueryBuilder('formData')
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
        ), function (?string $value) {
            return $value !== null;
        });
    }

    protected function initializeAccessibleDimensions(): array
    {
        return array_filter(array_map(
            function (FormData $formDataSample) {
                return $this->dimensionAccesibilityService->isDimensionCombinationAccessible($formDataSample->getContentDimensions()) ? $formDataSample->getDimensionsHash() : null;
            },
            $this->findAllUnique('form.dimensionsHash')
        ), function (?string $value) {
            return $value !== null;
        });
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
