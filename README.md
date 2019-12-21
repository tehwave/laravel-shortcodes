# Laravel Shortcodes

Simple, elegant WordPress-like Shortcodes the Laravel way

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
![Build Status](https://github.com/tehwave/laravel-shortcodes/workflows/tests/badge.svg)

## Requirements

- Laravel 6.x
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

```bash
php artisan make:shortcode ItalicizeText
```

This command will place a fresh `Shortcode` class in your new `app/Shortcodes` directory.

Each `Shortcode` class contains a `handle` method, that you may use to output into the compiling content.

Within the `handle` method, you may access the ```attributes``` and ```body``` properties.

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

The shortcode's tag is derived from the class name to snake_case. You may specify a custom tag using the ```tag``` property or overwriting the ```getTag``` method.

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

```php
use tehwave\Shortcodes\Shortcode;

$compiledContent = Shortcode::compile('[italics escape_html="true"]<b>Hello World</b>[/italics]');

// <i>&lt;b&gt;Hello World&lt;/b&gt;</i>
```

## Tests

```bash
composer test
```

## Security

For any security related issues, send a mail to [peterchrjoergensen+shortcodes@gmail.com](mailto:peterchrjoergensen+shortcodes@gmail.com) instead of using the issue tracker.

## Credits

- [Peter Jørgensen](https://github.com/tehwave)
- [All Contributors](../../contributors)

Inspired by https://github.com/webwizo/laravel-shortcodes and https://github.com/spatie/laravel-blade-x

## About

I work as a Web Developer in Denmark on Laravel and WordPress websites.

Follow me [@tehwave](https://twitter.com/tehwave) on Twitter!

## License

[MIT License](LICENSE)
