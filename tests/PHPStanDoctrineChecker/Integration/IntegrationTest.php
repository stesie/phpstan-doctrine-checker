<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration;

use PHPStan\Analyser\Analyser;
use PHPStan\File\FileHelper;
use PHPStanDoctrineChecker\TestCase;

class IntegrationTest extends TestCase
{
    public function testBasicViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationTest.php');
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame('DQL Query uses invalid filtered fetch-join', $error->getMessage());
        $this->assertSame(19, $error->getLine());
    }

    public function testBasicViolationWithChainedCalls()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/BasicViolationCallChainTest.php');
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame('DQL Query uses invalid filtered fetch-join', $error->getMessage());
        $this->assertSame(11, $error->getLine());
    }

    public function testRangeFilterUse()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/RangeFilterTest.php');
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame('DQL Query uses setFirstResult/setMaxResults with fetch-join', $error->getMessage());
        $this->assertSame(11, $error->getLine());
    }

    /**
     * @param string $file
     * @return \PHPStan\Analyser\Error[]|string[]
     */
    private function runAnalyse(string $file): array
    {
        $file = $this->getFileHelper()->normalizePath($file);

        /** @var Analyser $analyser */
        $analyser = $this->getContainer()->getByType(Analyser::class);

        /** @var FileHelper $fileHelper */
        $fileHelper = $this->getContainer()->getByType(FileHelper::class);

        $errors = $analyser->analyse([$file], false);
        foreach ($errors as $error) {
            $this->assertSame($fileHelper->normalizePath($file), $error->getFile());
        }

        return $errors;
    }
}
