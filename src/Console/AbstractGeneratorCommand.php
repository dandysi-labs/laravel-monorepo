<?php

declare(strict_types=1);

namespace Dandysi\Laravel\Monorepo\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractGeneratorCommand extends GeneratorCommand
{
    abstract protected function getStub();

    /**
     * Get the new class filename
     *
     * @return string
     */
    abstract protected function getFilename(): string;

    /**
     * Get the directory argument from the input
     *
     * @return string
     */
    protected function getDirectoryInput(): string
    {
        $directory = $this->argument('directory');
        if (empty($directory)) {
            return '';
        }

        return str_replace('\\', '/', trim($this->argument('directory')));
    }

    /**
     * Trime the end portion of a given string with the specified string
     *
     * @param string $subject
     * @param string $end
     * @return string
     */
    protected function trimEnd(string $subject, string $end): string
    {
        $pos = mb_strrpos($subject, $end);

        if ($pos === false) {
            return $subject;
        }

        return mb_substr($subject, 0, $pos);
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the '.strtolower($this->type)],
            ['directory', $this->isDirectoryOptional() ? InputArgument::OPTIONAL : InputArgument::REQUIRED, 'The name of the destination directory'],
        ];
    }

    /**
     * Determine if the directory arfument is optionals
     *
     * @return bool
     */
    protected function isDirectoryOptional(): bool
    {
        return true;
    }

    protected function getNameInput(): string
    {
        return str_replace('\\', '/', parent::getNameInput()) . '/'. $this->getFilename();
    }
}
