<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration\data;

use Doctrine\ORM\EntityRepository;

class BasicAcceptableFilter
{
    public function run(EntityRepository $repository)
    {
        $repository->createQueryBuilder('u')
            ->select('u', 'p')
            ->innerJoin('u.phoneNumbers', 'p')
            ->where('u.name = :name')
            ->setParameter('name', 'Rolf')
            ->getQuery()
            ->getSingleResult();
    }
}
