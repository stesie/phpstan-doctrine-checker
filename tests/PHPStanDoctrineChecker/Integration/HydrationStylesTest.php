<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration;

class HydrationStylesTest extends IntegrationTestCase
{
    public function testObjectHydrationWithGetResult()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ObjectHydrationWithGetResult.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 11, $errors);
    }
}
