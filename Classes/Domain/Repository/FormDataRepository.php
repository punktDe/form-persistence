<?php

declare(strict_types=1);

namespace PunktDe\Form\Persistence\Domain\Repository;

/*
 *  (c) 2020 punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

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
        $queryBuilder->groupBy('form.formIdentifier')
            ->addGroupBy('form.hash');
        return $queryBuilder->getQuery()->execute();
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
}
