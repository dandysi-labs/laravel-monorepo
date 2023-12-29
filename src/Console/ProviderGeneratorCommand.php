<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Console;

class ProviderGeneratorCommand extends AbstractGeneratorCommand
{
    protected $name = 'make:monorepo-provider';

    protected $description = 'Create a new monorepo provider';

    protected $type = 'Monorepo Provider';

    protected function getStub()
    {
        return __DIR__ . '/stubs/provider.stub';
    }

    protected function getFilename(): string
    {
        return 'MonorepoProvider';
    }

    protected function rootNamespace(): string
    {
        if ($this->getDirectoryInput()) {
            return str_replace('/', '\\', $this->trimEnd($this->getNameInput(), '/' . $this->getFilename()));
        }

        return parent::rootNamespace();
    }

    protected function getPath($name): string
    {
        $dir = $this->getDirectoryInput();

        if (!empty($dir)) {
            return $this->laravel['path.base'] . '/'. $dir . '/' . $this->getFilename() .'.php';
        }

        return parent::getPath($name);
    }
}
