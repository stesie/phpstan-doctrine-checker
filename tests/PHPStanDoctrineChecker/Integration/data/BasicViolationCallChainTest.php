<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class BasicViolationCallChainTest
{
    public function run(EntityRepository $repository)
    {
        $user = $repository->createQueryBuilder('u')
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p')
            ->where('p.type = :type')
            ->setParameter('type', 'work')
            ->getQuery()
            ->getSingleResult();
    }
}
