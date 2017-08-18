<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration;

use PHPStan\Analyser\Analyser;
use PHPStan\File\FileHelper;
use PHPStanDoctrineChecker\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    /**
     * @param string $file
     * @return \PHPStan\Analyser\Error[]|string[]
     */
    protected function runAnalyse(string $file): array
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

    /**
     * @param \PHPStan\Analyser\Error[]|string[] $errors
     */
    protected function assertNoErrors($errors)
    {
        $this->assertCount(0, $errors);
    }

    /**
     * @param string $expectedMessage
     * @param int $expectedLineNumber
     * @param \PHPStan\Analyser\Error[]|string[] $errors
     */
    protected function assertSingleError(string $expectedMessage, int $expectedLineNumber, $errors)
    {
        $this->assertCount(1, $errors);
        $error = $errors[0];
        $this->assertSame($expectedMessage, $error->getMessage());
        $this->assertSame($expectedLineNumber, $error->getLine());
    }
}
