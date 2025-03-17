<?php

namespace tehwave\Shortcodes\Tests;

use Illuminate\Support\Collection;
use tehwave\Shortcodes\Compiler;
use tehwave\Shortcodes\Shortcode;
use tehwave\Shortcodes\Tests\Shortcodes\OutputAttributes;
use tehwave\Shortcodes\Tests\Shortcodes\OutputBody;

class ShortcodeTest extends TestCase
{
    /**
     * A list of test shortcodes.
     *
     * @var \Illuminate\Support\Collection
     */
    private $shortcodes;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->shortcodes = collect([
            new OutputBody,
            new OutputAttributes,
        ]);
    }

    /**
     * Test that non-existing shortcodes are not being compiled.
     */
    public function test_shortcode_not_is_compiled(): void
    {
        if (file_exists($path = $this->app->path('Shortcodes').'/HelloWorld.php')) {
            unlink($path);

            Shortcode::clearCache();
        }

        $content = '[hello_world]';

        $compiledContent = Shortcode::compile($content);

        $this->assertSame($compiledContent, $content);
    }

    /**
     * Test that shortcode syntax can be escaped.
     */
    public function test_shortcode_is_escaped(): void
    {
        $compiledContent = Compiler::compile('[[output_body]]', $this->shortcodes);

        $this->assertSame('[output_body]', $compiledContent);
    }

    /**
     * Test that shortcode syntax can be escaped.
     */
    public function test_shortcode_body_is_parsed(): void
    {
        $compiledContent = Compiler::compile('[output_body]Hello World[/output_body]', $this->shortcodes);

        $this->assertSame('Hello World', $compiledContent);
    }

    /**
     * Test that no attributes are being parsed.
     */
    public function test_shortcode_attributes_is_not_parsed(): void
    {
        $compiledContent = Compiler::compile('[output_attributes]', $this->shortcodes);

        $expected = print_r(null, true);

        $this->assertSame($expected, $compiledContent);
    }

    /**
     * Test that any unclosed HTML tags are being rejected from attributes.
     */
    public function test_shortcode_attributes_reject_unclosed_html_tags(): void
    {
        $compiledContent = Compiler::compile('[output_attributes html="<h1>Hello World<"]', $this->shortcodes);

        $expected = print_r(null, true);

        $this->assertSame($expected, $compiledContent);
    }

    /**
     * Test the various syntaxes for attributes.
     *
     * @link https://unit-tests.svn.wordpress.org/trunk/tests/shortcode.php
     */
    public function test_shortcode_attributes_syntaxes(): void
    {
        collect([
            '[output_attributes /]' => '',
            '[output_attributes https://www.youtube.com/watch?v=dQw4w9WgXcQ]' => [0 => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            '[output_attributes foo]' => [0 => 'foo'],
            '[output_attributes foo="bar"]' => ['foo' => 'bar'],
            '[output_attributes foo="bar" /]' => ['foo' => 'bar'],
            "[output_attributes 'foo bar']" => [0 => 'foo bar'],
            "[output_attributes foo='bar']" => ['foo' => 'bar'],
            '[output_attributes 123 http://wordpress.com/ 0 "foo" bar]' => [0 => '123', 1 => 'http://wordpress.com/', 2 => 0, 3 => 'foo', 4 => 'bar'],
            '[output_attributes 123 url=http://wordpress.com/ foo bar="baz"]' => [0 => '123', 'url' => 'http://wordpress.com/', 1 => 'foo', 'bar' => 'baz'],
            '[output_attributes foo="bar" baz="bing"]content[/output_attributes]' => ['foo' => 'bar', 'baz' => 'bing'],
        ])->each(function ($output, $tag) {
            $compiledContent = Compiler::compile($tag, $this->shortcodes);

            $expected = $output;

            if (is_array($output)) {
                $expected = print_r($output, true);
            }

            $this->assertSame($expected, $compiledContent);
        });
    }

    /**
     * Test that the getTag method returns the correct tag.
     */
    public function test_get_tag(): void
    {
        $shortcode = new OutputBody;
        $this->assertSame('output_body', $shortcode->getTag());

        $shortcode = new OutputAttributes;
        $this->assertSame('output_attributes', $shortcode->getTag());
    }

    /**
     * Test that the dispatch method correctly handles shortcodes.
     */
    public function test_dispatch(): void
    {
        $shortcode = new class extends Shortcode
        {
            public function handle(): ?string
            {
                return 'Handled Content';
            }
        };

        $matches = [
            '[example]', // shortcode
            '', // prefix
            'example', // tag
            '', // attributes
            '', // tagClose
            '', // body
            '', // suffix
        ];

        $this->assertSame('Handled Content', $shortcode->dispatch($matches));
    }

    /**
     * Test that the getClasses method returns the correct classes.
     */
    public function test_get_classes(): void
    {
        $classes = Shortcode::getClasses();

        $this->assertInstanceOf(Collection::class, $classes);
    }

    /**
     * Test that the getNamespacedClasses method returns the correct namespaced classes.
     */
    public function test_get_namespaced_classes(): void
    {
        $namespacedClasses = Shortcode::getNamespacedClasses();

        $this->assertInstanceOf(Collection::class, $namespacedClasses);
    }

    /**
     * Test that the getInstantiatedClasses method returns the correct instances.
     */
    public function test_get_instantiated_classes(): void
    {
        $instances = Shortcode::getInstantiatedClasses();

        $this->assertInstanceOf(Collection::class, $instances);
    }

    /**
     * Test that the all method returns all instantiated classes.
     */
    public function testAll(): void
    {
        $all = Shortcode::all();

        $this->assertInstanceOf(Collection::class, $all);
    }

    /**
     * Test that the compile method compiles content correctly.
     */
    public function test_compile(): void
    {
        $content = '[example]';
        $compiledContent = Shortcode::compile($content);

        $this->assertSame($content, $compiledContent);
    }
}
