<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests\Feature;

use Dandysi\Laravel\Monorepo\Tests\PackageTestCase;

class GeneratorTestCase extends PackageTestCase
{
    protected function assertFileCreated(string $file, array $expectedStrings): void
    {
        $this->assertFileExists($file);
        $contents = file_get_contents($file);

        foreach ($expectedStrings as $expectedString) {
            $this->assertStringContainsString($expectedString, $contents);
        }

        //remove created file
        $this->removeFile($file);
    }

    protected function removeFile($file): void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}