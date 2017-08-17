# PHPStan Doctrine (Fetch-Join) Checker

[![Build Status](https://travis-ci.org/stesie/phpstan-doctrine-checker.svg?branch=master)](https://travis-ci.org/stesie/phpstan-doctrine-checker)
[![Coverage Status](https://coveralls.io/repos/github/stesie/phpstan-doctrine-checker/badge.svg?branch=master)](https://coveralls.io/github/stesie/phpstan-doctrine-checker?branch=master)
![PHPStan](https://img.shields.io/badge/style-level%207-brightgreen.svg?style=flat-square&label=phpstan)
[![License](https://poser.pugx.org/stesie/phpstan-doctrine-checker/license)](https://packagist.org/packages/stesie/phpstan-doctrine-checker)

PHPStan Doctrine Checker is an extension for [PHPStan](https://github.com/phpstan/phpstan), that provides
extra checks for PHPStan.

This project wouldn't be possible without the wonderful PHPStan tool.  If you consider using this
extension, you really also should add the [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine)
extension.

*So far this is just a proof of concept and not really usable*

The foremost goal is to find uses of Doctrine's QueryBuilder to construct (invalid) fetch-join queries
over filtered associations.  The problem with these is that they easily go undetected and blow off
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

Here the associated `phoneNumbers` are fetch-joined and also filtered.  If this query is executed
another time, maybe for all numbers with type "home", then Doctrine will *not* re-hydrate the
already hydrated `User` entities, and hence those will still have the work phone-number attached.


## Usage

To use this extension, require it with [composer](https://getcomposer.org/):

```
composer require --dev stesie/phpstan-doctrine-checker=@dev
```

And include `src/phpstan.neon` in your project's PHPStan config:

```
includes:
	- vendor/stesie/phpstan-doctrine-checker/src/phpstan.neon
```

## Roadmap

So far this project is more a proof of concept, less really usable.

Stuff I would really like to see implemented

- [ ] handle all `getResult` variants that hydrate to objects (and ignore the rest)
- [ ] more robust interpreting of various `$qb->expr()->xxx` filtering
- [ ] don't warn on filtering on (non-nullable?) `xxxToOne` relations, as those should eliminate the root
      (and hence not lead to partly hydrated objects)
- [ ] back-propagate filtering done on inner join'ed related tables, i.e. if you fetch join
      foo and bar tables, but baz (which depends on bar) is filtered ... then if baz is filtered
      out, it'll eliminate bar and (maybe) only partly hydrate foo.
- [ ] parse raw DQL

## Contributing

Any contributions are welcome. 
