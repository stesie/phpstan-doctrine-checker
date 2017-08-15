<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPUnit\Framework\TestCase;

class QueryBuilderTracerTest extends TestCase
{
    public function testComparisonOfLiteralBooleans()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('true = true', $qbInfo);

        $this->assertEmpty($qbInfo->getDirtyAliases());
    }

    public function testComparisonOfLiteralNumbers()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('23 != 42', $qbInfo);

        $this->assertEmpty($qbInfo->getDirtyAliases());
    }

    public function testComparisonOfFieldWithLiteralString()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name = \'Rolf\'', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonOfFieldWithLiteralStringYodaStyle()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('\'Rolf\' = u.name', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonOfSameFieldWithItself()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name = u.name', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonOfTwoFields()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u1.name = u2.name', $qbInfo);

        $this->assertSame(['u1', 'u2'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonOfFieldWithParameter()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name = :name', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonWithAnd()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name = \'Rolf\' AND p.type = \'work\'', $qbInfo);

        $this->assertSame(['u', 'p'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonWithMultipleAnd()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString(
            'u.name = \'Rolf\' AND p.type = \'work\' AND 1 = 2 AND 1 = 2 AND 1 = 2',
            $qbInfo
        );

        $this->assertSame(['u', 'p'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonWithOr()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name = \'Rolf\' OR p.type = \'work\'', $qbInfo);

        $this->assertSame(['u', 'p'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonWithMultipleOr()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString(
            'u.name = \'Rolf\' OR p.type = \'work\' OR 1 = 2 OR 1 = 2 OR 1 = 2',
            $qbInfo
        );

        $this->assertSame(['u', 'p'], $qbInfo->getDirtyAliases());
    }

    public function testComparisonWithNot()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('NOT u.name = \'Rolf\'', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }

    public function testConditionalExpressionWithinPrimary()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name = \'Rolf\' OR (1 = 2 AND p.type = \'work\')', $qbInfo);

        $this->assertSame(['u', 'p'], $qbInfo->getDirtyAliases());
    }

    public function testBetween()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.age BETWEEN 23 AND 42', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }

    public function testLike()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name LIKE \'x%\'', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }

    public function testIn()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name IN (\'x%\')', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }

    public function testParameterIsNull()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString(':foo IS NULL', $qbInfo);

        $this->assertSame([], $qbInfo->getDirtyAliases());
    }

    public function testFieldIsNull()
    {
        $qbInfo = new QueryBuilderInfo('x');
        (new QueryBuilderTracer())->processConditionString('u.name IS NOT NULL', $qbInfo);

        $this->assertSame(['u'], $qbInfo->getDirtyAliases());
    }
}
