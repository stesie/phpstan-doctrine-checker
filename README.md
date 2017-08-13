# PHPStan Doctrine Checker

PHPStan Doctrine Checker is an extension for [PHPStan](https://github.com/phpstan/phpstan), that provides
extra checks for PHPStan.

*So far this is just a proof of concept and not really usable*

The foremost goal is to find use of Doctrine's QueryBuilder to construct (invalid) fetch-join queries
over filtered associations.  The problem with these is, that they easily go undetected and blow off
at a totally different point in your code.

Consider this:

```php
$user = $this->_em->createQueryBuilder()
    ->select('u', 'p')
    ->from(User::class, 'u')
    ->join('u.phoneNumbers', 'p')
    ->where('p.type = :type')->setParameter('type', 'work')
    ->getQuery()
    ->getResult();
```

Here the associtaed `phoneNumbers` are fetch-joined and also filtered.  If this query is executed
another time, maybe for all numbers with type "home", then Doctrine will *not* re-hydrate the
already hydrated `User` entities, and hence those will still have the work phone-number attached.
