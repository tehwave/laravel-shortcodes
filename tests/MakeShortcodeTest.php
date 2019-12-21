<?php

namespace tehwave\Shortcodes\Tests;

class MakeShortcodeTest extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists($path = $this->app->path('Shortcodes') . '/HelloWorld.php')) {
            unlink($path);
        }
    }

    /**
     * Test the console command.
     *
     * @return void
     */
    public function testCommandMakesFile(): void
    {
        $this->artisan('make:shortcode', ['name' => 'HelloWorld'])->assertExitCode(0);

        $this->assertFileExists($this->app->path('Shortcodes') . '/HelloWorld.php');
    }
}
