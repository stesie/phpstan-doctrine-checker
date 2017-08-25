<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class BasicViolationConditionStringTypeExpression
{
    public function run(EntityRepository $repository)
    {
        $queryBuilder = $repository->createQueryBuilder('u');
        $condition = $queryBuilder->expr()->isNull('p.type');

        $queryBuilder
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p')
            ->where($condition)
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
