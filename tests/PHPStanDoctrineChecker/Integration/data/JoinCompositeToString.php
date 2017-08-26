<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class JoinCompositeToString
{
    public function run(EntityRepository $repository)
    {
        $queryBuilder = $repository->createQueryBuilder('u');

        $queryBuilder
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p', Expr\Join::WITH, $queryBuilder->expr()->andX('p.type = :type')->__toString())
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
