<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration;

class BasicAcceptanceTest extends IntegrationTestCase
{
    public function testBasicAcceptableFiltering()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicAcceptableFilter.php');
        $this->assertNoErrors($errors);
    }

    public function testBasicViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 19, $errors);
    }

    public function testBasicViolationWithChainedCalls()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationCallChainTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 11, $errors);
    }

    public function testBasicViolationInInnerJoin()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationInInnerJoinTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 11, $errors);
    }

    public function testBasicViolationInLeftJoin()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationInLeftJoinTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 11, $errors);
    }

    public function testBasicViolationInLeftJoinWithCastQueryBuilder()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationInLeftJoinWithCastQueryBuilderTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 13, $errors);
    }

    public function testBasicViolationArraySelect()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationArraySelect.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 11, $errors);
    }

    public function testBasicViolationConditionInVariable()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationConditionInVariable.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 14, $errors);
    }

    public function testComparisonToString()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ComparisonToString.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 14, $errors);
    }

    public function testCompositeToString()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/CompositeToString.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 14, $errors);
    }

    public function testJoinCompositeToString()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/JoinCompositeToString.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 14, $errors);
    }

    public function testBasicViolationConditionNotMergedVariable()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationConditionNotMergedVariable.php');
        $this->assertNoErrors($errors);
    }

    public function testBasicViolationConditionStringTypeExpression()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationConditionStringTypeExpression.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 14, $errors);
    }

    public function testExprAll()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprAllTest.php');
        $this->assertNoErrors($errors);
    }

    public function testExprEqSubQuery()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprEqSubQuery.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 16, $errors);
    }

    public function testExprInSubQuery()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprInSubQuery.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join on p', 16, $errors);
    }

    public function testRangeFilterUse()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/RangeFilterTest.php');
        $this->assertSingleError('DQL Query uses setFirstResult/setMaxResults with fetch-join', 11, $errors);
    }
}
