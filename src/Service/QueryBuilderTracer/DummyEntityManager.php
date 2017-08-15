<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service\QueryBuilderTracer;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use PHPStanDoctrineChecker\Exceptions\NotImplementedException;

class DummyEntityManager implements EntityManagerInterface
{
    public function getConfiguration()
    {
        return new Configuration();
    }

    public function getCache()
    {
        throw new NotImplementedException();
    }

    public function getConnection()
    {
        throw new NotImplementedException();
    }

    public function getExpressionBuilder()
    {
        throw new NotImplementedException();
    }

    public function beginTransaction()
    {
        throw new NotImplementedException();
    }

    public function transactional($func)
    {
        throw new NotImplementedException();
    }

    public function commit()
    {
        throw new NotImplementedException();
    }

    public function rollback()
    {
        throw new NotImplementedException();
    }

    public function createQuery($dql = '')
    {
        throw new NotImplementedException();
    }

    public function createNamedQuery($name)
    {
        throw new NotImplementedException();
    }

    public function createNativeQuery($sql, ResultSetMapping $rsm)
    {
        throw new NotImplementedException();
    }

    public function createNamedNativeQuery($name)
    {
        throw new NotImplementedException();
    }

    public function createQueryBuilder()
    {
        throw new NotImplementedException();
    }

    public function getReference($entityName, $id)
    {
        throw new NotImplementedException();
    }

    public function getPartialReference($entityName, $identifier)
    {
        throw new NotImplementedException();
    }

    public function close()
    {
        throw new NotImplementedException();
    }

    public function copy($entity, $deep = false)
    {
        throw new NotImplementedException();
    }

    public function lock($entity, $lockMode, $lockVersion = null)
    {
        throw new NotImplementedException();
    }

    public function getEventManager()
    {
        throw new NotImplementedException();
    }

    public function isOpen()
    {
        throw new NotImplementedException();
    }

    public function getUnitOfWork()
    {
        throw new NotImplementedException();
    }

    public function getHydrator($hydrationMode)
    {
        throw new NotImplementedException();
    }

    public function newHydrator($hydrationMode)
    {
        throw new NotImplementedException();
    }

    public function getProxyFactory()
    {
        throw new NotImplementedException();
    }

    public function getFilters()
    {
        throw new NotImplementedException();
    }

    public function isFiltersStateClean()
    {
        throw new NotImplementedException();
    }

    public function hasFilters()
    {
        throw new NotImplementedException();
    }

    public function find($className, $id)
    {
        throw new NotImplementedException();
    }

    public function persist($object)
    {
        throw new NotImplementedException();
    }

    public function remove($object)
    {
        throw new NotImplementedException();
    }

    public function merge($object)
    {
        throw new NotImplementedException();
    }

    public function clear($objectName = null)
    {
        throw new NotImplementedException();
    }

    public function detach($object)
    {
        throw new NotImplementedException();
    }

    public function refresh($object)
    {
        throw new NotImplementedException();
    }

    public function flush()
    {
        throw new NotImplementedException();
    }

    public function getRepository($className)
    {
        throw new NotImplementedException();
    }

    public function getMetadataFactory()
    {
        throw new NotImplementedException();
    }

    public function initializeObject($obj)
    {
        throw new NotImplementedException();
    }

    public function contains($object)
    {
        throw new NotImplementedException();
    }

    public function getClassMetadata($className)
    {
        throw new \LogicException('not implemented');
    }
}
