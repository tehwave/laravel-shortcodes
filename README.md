![](https://banners.beyondco.de/Laravel%20Shortcodes.jpeg?theme=light&packageManager=composer+require&packageName=tehwave%2Flaravel-shortcodes&pattern=wiggle&style=style_1&description=Simple%2C+elegant+WordPress-like+Shortcodes+the+Laravel+way&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

# Laravel Shortcodes

Simple, elegant WordPress-like Shortcodes the Laravel way.

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
![Build Status](https://github.com/tehwave/laravel-shortcodes/workflows/tests/badge.svg)

## Requirements

The package has been developed and tested to work with the latest supported versions of PHP and Laravel as well as the following minimum requirements:

- Laravel 11
- PHP 8.2

### Version Compatibility

| Laravel | PHP | Branch |
|---|---|---|
|  11+ | 8.2+ | [master](https://github.com/tehwave/laravel-shortcodes/tree/master) |
|  10 and below | 8.1 and below | [1.x](https://github.com/tehwave/laravel-shortcodes/tree/1.x) |

## Installation

Install the package via Composer.

```bash
composer require tehwave/laravel-shortcodes
```

## Usage

`Laravel Shortcodes` work much like WordPress' [Shortcode API](https://codex.wordpress.org/Shortcode_API).

```php
<?php

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

> [!NOTE]  
> All values in the `attributes` array are cast to `string` type when parsed unless specifically cast to a type via the `$casts` property.

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
<?php

use tehwave\Shortcodes\Shortcode;

$compiledContent = Shortcode::compile('[italics escape_html="true"]<b>Hello World</b>[/italics]');

// <i>&lt;b&gt;Hello World&lt;/b&gt;</i>
```

You may specify a list of instantiated `Shortcode` classes to limit what shortcodes are parsed.

```php
<?php

use tehwave\Shortcodes\Shortcode;

$shortcodes = collect([
    new ItalicizeText,
]);

$compiledContent = Shortcode::compile('[uppercase]Hello World[/uppercase]', $shortcodes);

// [uppercase]Hello World[/uppercase]
```

### Using Casts

`Laravel Shortcodes` supports casting attributes to various data types. This can be useful when you need to ensure that the attributes passed to your shortcodes are of a specific type.

#### Available Casts

- `boolean`
- `integer`
- `float`
- `string`
- `array`
- `collection`
- `object`
- `json`
- `encrypted`
- `hashed`
- `date` (casts to `Carbon\Carbon` instance)

#### Example

To use casts, you need to create a shortcode class and specify the casts in the `$casts` property.

```php
<?php

namespace App\Shortcodes;

use tehwave\Shortcodes\Shortcode;
use Carbon\Carbon;

class ExampleShortcode extends Shortcode
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'count' => 'integer',
        'price' => 'float',
        'name' => 'string',
        'tags' => 'array',
        'options' => 'collection',
        'metadata' => 'object',
        'config' => 'json',
        'published_at' => 'date',
    ];

    /**
     * The code to run when the Shortcode is being compiled.
     *
     * @return string|null
     */
    public function handle(): ?string
    {
        $publishedAt = $this->attributes['published_at'] instanceof Carbon
            ? $this->attributes['published_at']->toFormattedDateString()
            : 'N/A';

        $tags = implode(', ', $this->attributes['tags']);

        $options = $this->attributes['options']->implode(', ');

        return sprintf(
            'Active: %s, Count: %d, Price: %.2f, Name: %s, Published At: %s, Tags: %s, Options: %s',
            $this->attributes['is_active'] === true ? 'Yes' : 'No',
            $this->attributes['count'],
            $this->attributes['price'],
            $this->attributes['name'],
            $publishedAt,
            $tags,
            $options
        );
    }
}
```

When you compile content with this shortcode, the attributes will be automatically cast to the specified types.

```php
<?php

use tehwave\Shortcodes\Shortcode;

$compiledContent = Shortcode::compile('[example is_active="1" count="10" price="99.99" name="Sample" published_at="2023-06-29" tags=\'["tag1","tag2","tag3"]\' options=\'["option1","option2"]\']');

// Active: Yes, Count: 10, Price: 99.99, Name: Sample, Published At: Jun 29, 2023, Tags: tag1, tag2, tag3, Options: option1, option2
```

### Accessing Attributes

You can retrieve the attributes as direct properties of the shortcode instance.

```php
<?php

namespace App\Shortcodes;

use tehwave\Shortcodes\Shortcode;

class ExampleShortcode extends Shortcode
{
    protected $casts = [
        'is_active' => 'boolean',
        'count' => 'integer',
    ];

    public function handle(): ?string
    {
        // Access attributes as properties
        $isActive = $this->is_active;
        $count = $this->count;

        return sprintf('Active: %s, Count: %d', $isActive === true ? 'Yes' : 'No', $count);
    }
}
```

### Example

I developed `Laravel Shortcodes` for use with user provided content on [gm48.net](https://gm48.net).

The content is parsed using a Markdown converter called Parsedown, and because users can't be trusted, the content has to be escaped.

Unfortunately, this escapes the attribute syntax with double quotes, but singular quotes can still be used as well as just omitting any quotes.

> [!NOTE]  
> Quotes are required for any attribute values that contain whitespace.

Let's take a look at the following content with some basic `Row`, `Column`and `Image` shortcodes.

```
# Controls:

[row]
    [column]
        [image align=left src=http://i.imgur.com/6CNoFYx.png alt='Move player character']
    [/column]
    [column]
        [image align=center src=http://i.imgur.com/8nwaVo0.png alt=Jump]
    [/column]
    [column]
        [image align=right src=http://i.imgur.com/QsbkkuZ.png alt='Go down through platforms']
    [/column]
[/row]
```

When running the content through the following code:

```php
$parsedDescription = (new Parsedown())
    ->setSafeMode(true)
    ->setUrlsLinked(false)
    ->text($this->description);

$compiledDescription = Shortcode::compile($parsedDescription);
```

We can expect to see the following output:

```html
<h1>Controls:</h1>
<p></p>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <img src="http://i.imgur.com/6CNoFYx.png" class="mr-auto" alt="Move player character">
        </div>
        <div class="col">
            <img src="http://i.imgur.com/8nwaVo0.png" class="mx-auto" alt="Jump">
        </div>
        <div class="col">
            <img src="http://i.imgur.com/QsbkkuZ.png" class="ml-auto" alt="Go down through platforms">
        </div>
    </div>
</div>
```

You should still escape any user input within your shortcodes' `handle`.

## Tests

Run the following command to test the package.

```bash
composer test
```

## Security

For any security related issues, send a mail to [peterchrjoergensen+shortcodes@gmail.com](mailto:peterchrjoergensen+shortcodes@gmail.com) instead of using the issue tracker.

## Changelog

See [CHANGELOG](CHANGELOG.md) for details on what has changed.

## Upgrade Guide

See [UPGRADING.md](UPGRADING.md) for details on how to upgrade.

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
