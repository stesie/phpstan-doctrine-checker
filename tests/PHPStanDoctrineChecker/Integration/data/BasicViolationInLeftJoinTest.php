<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class BasicViolationInLeftJoinTest
{
    public function run(EntityRepository $repository)
    {
        $repository->createQueryBuilder('u')
            ->select('u', 'p')
            ->leftJoin('u.phoneNumbers', 'p', 'WITH', 'p.type = :type')
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
