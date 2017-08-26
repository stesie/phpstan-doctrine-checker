<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class CompositeToString
{
    public function run(EntityRepository $repository)
    {
        $queryBuilder = $repository->createQueryBuilder('u');
        $condition = $queryBuilder->expr()->andX('p.type = :type')->__toString();

        $queryBuilder
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p')
            ->where($condition)
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
