<?php

namespace tehwave\Shortcodes\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use tehwave\Shortcodes\Providers\ShortcodesServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            ShortcodesServiceProvider::class,
        ];
    }
}
