<?php

namespace tehwave\Shortcodes\Tests;

use tehwave\Shortcodes\Compiler;
use tehwave\Shortcodes\Shortcode;
use Illuminate\Support\Facades\Date;
use tehwave\Shortcodes\Tests\Shortcodes\CastDate;
use tehwave\Shortcodes\Tests\Shortcodes\CastFloat;
use tehwave\Shortcodes\Tests\Shortcodes\OutputBody;
use tehwave\Shortcodes\Tests\Shortcodes\CastBoolean;
use tehwave\Shortcodes\Tests\Shortcodes\CastInteger;
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
            new CastBoolean,
            new CastDate,
            new CastFloat,
            new CastInteger,
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

    /**
     * Test the various castings for attributes.
     *
     * @link https://unit-tests.svn.wordpress.org/trunk/tests/shortcode.php
     *
     * @return void
     */
    public function testShortcodeAttributesCasting(): void
    {
        collect([
            '[cast_boolean test-boolean]' => 'true',
            '[cast_boolean test-boolean="1"]' => 'true',
            '[cast_boolean test-boolean="0"]' => 'false',
            '[cast_boolean /]' => 'false',
            '[cast_date test-date="2023-06-29"]' => (string) Date::parse('2023-06-29')->timestamp,
            '[cast_date test-date="2020-01-01"]' => (string) Date::parse('2020-01-01')->timestamp,
            '[cast_integer test-int="3"]' => '6',
            '[cast_integer test-int="35460"]' => '70920',
            '[cast_float test-float="5.67"]' => '15.67',
            '[cast_float test-float="15.011"]' => '25.011',
        ])->each(function ($output, $tag) {
            $compiledContent = Compiler::compile($tag, $this->shortcodes);

            $expected = $output;

            $this->assertSame($expected, $compiledContent);
        });
    }
}
