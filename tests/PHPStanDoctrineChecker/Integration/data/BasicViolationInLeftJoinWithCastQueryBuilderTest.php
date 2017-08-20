<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class BasicViolationInLeftJoinWithCastQueryBuilderTest
{
    public function run(EntityRepository $repository)
    {
        $queryBuilder = $repository->createQueryBuilder('u');

        $queryBuilder
            ->select('u', 'p')
            ->leftJoin('u.phoneNumbers', 'p', 'WITH', (string) $queryBuilder->expr()->eq('p.type', ':type'))
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
