<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class ExprAllTest
{
    public function run(EntityRepository $repository)
    {
        $queryBuilder = $repository->createQueryBuilder('u');

        $queryBuilder
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p')
            ->andWhere(
                $queryBuilder->expr()->eq(
                    ':type',
                    $queryBuilder->expr()->all(
                        $repository->createQueryBuilder('u2')
                            ->select('p2.type')
                            ->join('u2.phoneNumbers', 'p2')
                            ->where('u.id = u2.id')
                    )
                )
            )
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
