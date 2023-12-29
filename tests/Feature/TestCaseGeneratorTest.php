<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests\Feature;

class TestCaseGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     * @dataProvider generateData
     */
    public function it_generates_a_test_case(string $commandArguments, string $expectedNamespace, string $expectedProviderNamespace, string $expectedFilePath): void
    {
        $expectedFile = $this->app['path.base'] . '/' . $expectedFilePath;
        $this->removeFile($expectedFile);

        $this
            ->artisan("make:monorepo-test-case $commandArguments")
            ->assertExitCode(0)
        ;

        $this->assertTestCaseFileCreated($expectedFile, $expectedNamespace, $expectedProviderNamespace);
    }

    public static function generateData(): array
    {
        return [
            ['Tests/Chores tests/Chores Chores', 'Tests\Chores', 'Chores', 'tests/Chores/TestCase.php'],
            ['/Tests/Chores tests/Chores Chores', 'Tests\Chores', 'Chores', 'tests/Chores/TestCase.php'],
            ['"Tests\\\\Chores" "tests\\\\Chores" Chores', 'Tests\Chores', 'Chores', 'tests/Chores/TestCase.php'],
            ['Chores/Tests microservices/Chores/Tests Chores', 'Chores\Tests', 'Chores', 'microservices/Chores/Tests/TestCase.php'],
        ];
    }

    private function assertTestCaseFileCreated(string $expectedFile, string $namespace, string $providerNamespace): void
    {
        $this->assertFileCreated($expectedFile, [
            'namespace ' . $namespace . ';',
            'use ' . $providerNamespace . '\MonorepoProvider;'
        ]);
    }
}