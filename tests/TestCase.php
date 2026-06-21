<?php

namespace tehwave\Shortcodes\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Testing\TestResponse;
use Orchestra\Testbench\TestCase as Orchestra;
use tehwave\Shortcodes\Providers\ShortcodesServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * This holds the latest HTTP response from a test.
     *
     * @var TestResponse|null
     */
    protected static $latestResponse;

    /**
     * @param  Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            ShortcodesServiceProvider::class,
        ];
    }
}
