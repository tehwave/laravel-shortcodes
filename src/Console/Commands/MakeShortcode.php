<?php

namespace tehwave\Shortcodes\Console\Commands

use Illuminate\Console\GeneratorCommand;

class MakeShortcode extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:shortcode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Shortcode class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Shortcode';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/Shortcode.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return "{$rootNamespace}\Shortcodes";
    }
}
