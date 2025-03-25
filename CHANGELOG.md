# Changelog

Any notable changes to `Laravel Shortcodes` will be documented in this file.

## v2.0.0 (XX-XX-XXXX)

### Versions compatibility changed

Older versions of Laravel and PHP have been dropped.

Laravel 11 and 12 as well as PHP 8.2, 8.3, and 8.4 are now supported.

| Laravel | PHP | Branch |
|---|---|---|
|  11+ | 8.2+ | [master](https://github.com/tehwave/laravel-shortcodes/tree/master) |
|  10 and below | 8.1 and below | [1.x](https://github.com/tehwave/laravel-shortcodes/tree/1.x) |

### Additions to attribute handling

#### Attribute Casting

The attribute casting feature has been introduced. Ensure that any attributes that need specific types are defined in the `$casts` property of your shortcode classes.

#### Accessing Attributes as Properties

Attributes can now be accessed directly as properties of the shortcode instance. This allows for more intuitive and readable code within the `handle` method of your shortcode classes.

#### Example

```php
<?php

namespace App\Shortcodes;

use tehwave\Shortcodes\Shortcode;

class Example extends Shortcode
{
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function handle(): ?string
    {
        return $this->is_active === true ? 'Yes' : 'No';
    }
}
```

```php
<?php

use tehwave\Shortcodes\Shortcode;

Shortcode::compile('[example is_active="1"]');

// Yes
```

## v1.4.0 (26-03-2023)

- Add support for `Laravel 10`

## v1.3.0 (21-04-2022)

- Add support for `Laravel 9`

## v1.2.0 (05-11-2020)

- Add support for `Laravel 8`
- Add support for `PHP 8`

## v1.1.0 (07-03-2020)

- Add support for `Laravel 7`

## v1.0.1 (19-01-2020)

- Fixed phpdocs
- Fixed Shortcode stub
- Add missing `string` typehint to `resolveAttributes`

## v1.0.0 (22-12-2019)

- First release
