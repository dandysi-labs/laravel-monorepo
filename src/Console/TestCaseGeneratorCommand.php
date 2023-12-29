<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Console;

use Symfony\Component\Console\Input\InputArgument;

class TestCaseGeneratorCommand extends AbstractGeneratorCommand
{
    protected $name = 'make:monorepo-test-case';

    protected $description = 'Create a new monorepo test case';

    protected $type = 'Monorepo Test Case';

    protected function getArguments(): array
    {
        $arguments = parent::getArguments();
        $arguments[] = ['provider', InputArgument::REQUIRED, 'The monorepo provider class'];
        return $arguments;
    }

    protected function isDirectoryOptional(): bool
    {
        return false;
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/test_case.stub';
    }

    protected function getFilename(): string
    {
        return 'TestCase';
    }

    protected function getProviderInput(): string
    {
        return str_replace('/', '\\', trim($this->argument('provider'))) . '\\MonorepoProvider';
    }

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        return str_replace(
            ['{{ namespace }}', '{{ monorepo_provider }}'],
            [$name, $this->getProviderInput()],
            $stub
        );
    }

    protected function getPath($name): string
    {
        return $this->laravel['path.base'] . '/' . str_replace('\\', '/', $this->argument('directory')) . '/'  .$this->getFilename() . '.php';
    }

    protected function qualifyClass($name): string
    {
        $name = $this->trimEnd($this->getNameInput(), '/' . $this->getFilename());
        $name = str_replace('/', '\\', $name);
        return ltrim($name, '\\');
    }
}
