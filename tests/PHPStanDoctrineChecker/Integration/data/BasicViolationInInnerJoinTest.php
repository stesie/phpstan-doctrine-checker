<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class BasicViolationInInnerJoinTest
{
    public function run(EntityRepository $repository)
    {
        $repository->createQueryBuilder('u')
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p', 'WITH', 'p.type = :type')
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
