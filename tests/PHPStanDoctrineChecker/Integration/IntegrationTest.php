<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration;

class IntegrationTest extends IntegrationTestCase
{
    public function testBasicAcceptableFiltering()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicAcceptableFilter.php');
        $this->assertNoErrors($errors);
    }

    public function testBasicViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 19, $errors);
    }

    public function testBasicViolationWithChainedCalls()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationCallChainTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 11, $errors);
    }

    public function testBasicViolationInInnerJoin()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationInInnerJoinTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 11, $errors);
    }

    public function testBasicViolationInLeftJoin()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationInLeftJoinTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 11, $errors);
    }

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

    public function testRangeFilterUse()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/RangeFilterTest.php');
        $this->assertSingleError('DQL Query uses setFirstResult/setMaxResults with fetch-join', 11, $errors);
    }

    public function testObjectHydrationWithGetResult()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ObjectHydrationWithGetResult.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 11, $errors);
    }
}
