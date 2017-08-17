<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class ExprLteViolationTest
{
    public function run(EntityRepository $repository)
    {
        $queryBuilder = $repository->createQueryBuilder('u');

        $queryBuilder
            ->select('u', 'p')
            ->innerJoin('u.personalInfo', 'p')
            ->where($queryBuilder->expr()->lte('p.age', ':maxAge'))
            ->setParameter('maxAge', 23)
            ->getQuery()
            ->getSingleResult();
    }
}
