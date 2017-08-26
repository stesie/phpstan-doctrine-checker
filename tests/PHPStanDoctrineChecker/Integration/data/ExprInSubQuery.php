<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class ExprInSubQuery
{
    public function run(EntityRepository $repository)
    {
        $subQueryBuilder = $repository->createQueryBuilder('su')
            ->select('MIN(sp.type)')
            ->innerJoin('su.phoneNumbers', 'sp');

        $queryBuilder = $repository->createQueryBuilder('u');
        $queryBuilder
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p')
            ->andWhere(
                $queryBuilder->expr()->in('p.type', $subQueryBuilder)
            )
            ->getQuery()
            ->getSingleResult();
    }
}
