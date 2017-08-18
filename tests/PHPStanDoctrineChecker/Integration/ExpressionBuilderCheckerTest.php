<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration;

class ExpressionBuilderCheckerTest extends IntegrationTestCase
{
    public function testExprEqViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprEqViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
    }

    public function testExprLteViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprLteViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
    }

    public function testExprOrXViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprOrXViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
    }

    public function testExprIsNullViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprIsNullViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
    }

    public function testExprInViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprInViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
    }
}
