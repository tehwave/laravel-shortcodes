<?php

namespace tehwave\Shortcodes\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use tehwave\Shortcodes\Providers\ShortcodesServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * This holds the latest HTTP response from a test.
     *
     * @var \Illuminate\Testing\TestResponse|null
     */
    protected static $latestResponse;

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
