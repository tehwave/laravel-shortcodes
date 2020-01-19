# Laravel Shortcodes

Simple, elegant WordPress-like Shortcodes the Laravel way.

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![StyleCI](https://styleci.io/repos/229155772/shield)](https://styleci.io/repos/229155772)
[![Quality Score](https://img.shields.io/scrutinizer/g/tehwave/laravel-shortcodes.svg?style=flat-square)](https://scrutinizer-ci.com/g/tehwave/laravel-shortcodes)
![Build Status](https://github.com/tehwave/laravel-shortcodes/workflows/tests/badge.svg)

## Requirements

The package has been developed and tested to work with the following minimum requirements:

- Laravel 6
- PHP 7.2

## Installation

Install the package via Composer.

```bash
composer require tehwave/laravel-shortcodes
```

## Usage

`Laravel Shortcodes` work much like Wordpress' [Shortcode API](https://codex.wordpress.org/Shortcode_API).

```php
use tehwave\Shortcodes\Shortcode;

$compiledContent = Shortcode::compile('[uppercase]Laravel Shortcodes[/uppercase]');

// LARAVEL SHORTCODES
```

### Creating Shortcodes

Run the following command to place a fresh `Shortcode` class in your new `app/Shortcodes` directory.

```bash
php artisan make:shortcode ItalicizeText
```

#### Output

Each `Shortcode` class contains a `handle` method, that you may use to output into the compiling content.

Within the `handle` method, you may access the `attributes` and `body` properties.

> **Note**: All values in the `attributes` array are casted to `string` type when parsed.

```php
<?php

namespace App\Shortcodes;

use tehwave\Shortcodes\Shortcode;

class ItalicizeText extends Shortcode
{
    /**
     * The code to run when the Shortcode is being compiled.
     *
     * You may return a string from here, that will then
     * be inserted into the content being compiled.
     *
     * @return string|null
     */
    public function handle(): ?string
    {
        if (isset($this->attributes['escape_html']) && $this->attributes['escape_html'] === 'true')) {
            return sprintf('<i>%s</i>', htmlspecialchars($this->body));
        }

        return sprintf('<i>%s</i>', $this->body);
    }
}
```

#### Naming

The shortcode's tag is derived from the class name to snake_case.

You may specify a custom tag using the `tag` property or by overwriting the `getTag` method.

Shortcode tags must be alpha-numeric characters and may include underscores.

```php
<?php

namespace App\Shortcodes;

use tehwave\Shortcodes\Shortcode;

class ItalicizeText extends Shortcode
{
    /**
     * The tag to match in content.
     *
     * @var string
     */
    protected $tag = 'italics';
}
```

### Compiling Shortcodes

Run a string through the compiler to parse all shortcodes.

```php
use tehwave\Shortcodes\Shortcode;

$compiledContent = Shortcode::compile('[italics escape_html="true"]<b>Hello World</b>[/italics]');

// <i>&lt;b&gt;Hello World&lt;/b&gt;</i>
```

You may specify a list of instantiated `Shortcode` classes to limit what shortcodes are parsed.

```php
use tehwave\Shortcodes\Shortcode;

$shortcodes = collect([
    new ItalicizeText,
]);

$compiledContent = Shortcode::compile('[uppercase]Hello World[/uppercase]', $shortcodes);

// [uppercase]Hello World[/uppercase]
```

## Tests

Run the following command to test the package.

```bash
composer test
```

## Security

For any security related issues, send a mail to [peterchrjoergensen+shortcodes@gmail.com](mailto:peterchrjoergensen+shortcodes@gmail.com) instead of using the issue tracker.

## Changelog

See [CHANGELOG](CHANGELOG.md) for details on what has changed.

## Contributions

See [CONTRIBUTING](CONTRIBUTING.md) for details on how to contribute.

## Credits

- [Peter Jørgensen](https://github.com/tehwave)
- [All Contributors](../../contributors)

Inspired by https://github.com/webwizo/laravel-shortcodes and https://github.com/spatie/laravel-blade-x

## About

I work as a Web Developer in Denmark on Laravel and WordPress websites.

Follow me [@tehwave](https://twitter.com/tehwave) on Twitter!

## License

[MIT License](LICENSE)
