<?php

namespace tehwave\Shortcodes\Tests;

use tehwave\Shortcodes\Compiler;
use tehwave\Shortcodes\Shortcode;
use tehwave\Shortcodes\Tests\Shortcodes\OutputBody;
use tehwave\Shortcodes\Tests\Shortcodes\OutputAttributes;

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
     *
     * @return void
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
     *
     * @return void
     */
    public function testShortcodeNotIsCompiled(): void
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
     *
     * @return void
     */
    public function testShortcodeIsEscaped(): void
    {
        $compiledContent = Compiler::compile('[[output_body]]', $this->shortcodes);

        $this->assertSame('[output_body]', $compiledContent);
    }

    /**
     * Test that shortcode syntax can be escaped.
     *
     * @return void
     */
    public function testShortcodeBodyIsParsed(): void
    {
        $compiledContent = Compiler::compile('[output_body]Hello World[/output_body]', $this->shortcodes);

        $this->assertSame('Hello World', $compiledContent);
    }

    /**
     * Test that no attributes are being parsed.
     *
     * @return void
     */
    public function testShortcodeAttributesIsNotParsed(): void
    {
        $compiledContent = Compiler::compile('[output_attributes]', $this->shortcodes);

        $expected = print_r(null, true);

        $this->assertSame($expected, $compiledContent);
    }

    /**
     * Test that any unclosed HTML tags are being rejected from attributes.
     *
     * @return void
     */
    public function testShortcodeAttributesRejectUnclosedHtmlTags(): void
    {
        $compiledContent = Compiler::compile('[output_attributes html="<h1>Hello World<"]', $this->shortcodes);

        $expected = print_r(null, true);

        $this->assertSame($expected, $compiledContent);
    }

    /**
     * Test the various syntaxes for attributes.
     *
     * @link https://unit-tests.svn.wordpress.org/trunk/tests/shortcode.php
     *
     * @return void
     */
    public function testShortcodeAttributesSyntaxes(): void
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
}
