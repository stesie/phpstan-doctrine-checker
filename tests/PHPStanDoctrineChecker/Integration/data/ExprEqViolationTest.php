<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class ExprEqViolationTest
{
    public function run(EntityRepository $repository)
    {
        $queryBuilder = $repository->createQueryBuilder('u');

        $queryBuilder
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p')
            ->where($queryBuilder->expr()->eq('p.type', ':type'))
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
