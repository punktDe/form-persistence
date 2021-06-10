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
use PunktDe\Form\Persistence\Domain\Model\FormData;

/**
 * @Flow\Scope("singleton")
 */
class FormDataRepository extends Repository
{

    protected $defaultOrderings = [
        'date' => QueryInterface::ORDER_DESCENDING,
    ];

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
     */
    public function findByFormIdentifierAndHash(string $formIdentifier, string $hash): QueryResultInterface
    {
        $query = $this->createQuery();

        return $query->matching(
            $query->logicalAnd(
                $query->equals('formIdentifier', $formIdentifier),
                $query->equals('hash', $hash)
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
                $query->like('formData', '%' . $data . '%')
            )
        )->execute()->getFirst();
        /** @var FormData $result */
        return $result;
    }
}
