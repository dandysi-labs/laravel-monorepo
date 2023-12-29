<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Tests\Feature;

class ProviderGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     * @dataProvider generateData
     */
    public function it_generates_a_provider(string $commandArguments, string $expectedNamespace, string $expectedFilePath): void
    {
        $expectedFile = $this->app['path.base'] . '/' . $expectedFilePath;
        $this->removeFile($expectedFile);

        $this
            ->artisan("make:monorepo-provider $commandArguments")
            ->assertExitCode(0)
        ;

        $this->assertProviderFileCreated($expectedFile, $expectedNamespace);
    }

    public static function generateData(): array
    {
        return [
            ['Chores', 'App\Chores', 'app/Chores/MonorepoProvider.php'],
            ['Microservices/Chores', 'App\Microservices\Chores', 'app/Microservices/Chores/MonorepoProvider.php'],
            ['"Microservices\\\\Chores"', 'App\Microservices\Chores', 'app/Microservices/Chores/MonorepoProvider.php'],  //
            ['Chores microservices/chores', 'Chores', 'microservices/chores/MonorepoProvider.php'],
            ['Chores "microservices\\\\chores"', 'Chores', 'microservices/chores/MonorepoProvider.php'],   //
            ['Microservices/Chores microservices/Chores', 'Microservices\Chores', 'microservices/Chores/MonorepoProvider.php'],
            ['"Microservices\\\\Chores" "microservices\\\\Chores"', 'Microservices\Chores', 'microservices/Chores/MonorepoProvider.php'], //
        ];
    }

    private function assertProviderFileCreated(string $expectedFile, string $namespace): void
    {
        $this->assertFileCreated($expectedFile, [
            'namespace ' . $namespace . ';'
        ]);
    }
}