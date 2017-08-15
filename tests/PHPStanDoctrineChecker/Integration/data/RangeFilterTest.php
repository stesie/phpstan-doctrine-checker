<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class RangeFilterTest
{
    public function run(EntityRepository $repository)
    {
        $repository->createQueryBuilder('u')
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
