<?php declare(strict_types = 1);

use Nette\Configurator;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase.php';

$rootDir = __DIR__ . '/..';
$tmpDir = $rootDir . '/tmp';
$confDir = $rootDir . '/vendor/phpstan/phpstan/conf';

$configurator = new Configurator();
$configurator->defaultExtensions = [];
$configurator->setDebugMode(true);
$configurator->setTempDirectory($tmpDir);
$configurator->addConfig($confDir . '/config.neon');
$configurator->addConfig($confDir . '/config.level5.neon');
$configurator->addConfig($rootDir . '/src/phpstan.neon');
$configurator->addParameters([
	'rootDir' => $rootDir,
	'tmpDir' => $tmpDir,
	'currentWorkingDirectory' => $rootDir,
	'cliArgumentsVariablesRegistered' => false,
]);
$container = $configurator->createContainer();

PHPStanDoctrineChecker\TestCase::setContainer($container);
PHPStan\Type\TypeCombinator::setUnionTypesEnabled(true);
