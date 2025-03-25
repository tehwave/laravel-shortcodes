# Upgrade Guide

## Upgrading from 1.x to 2.0

### Version Compatibility

Older versions of Laravel and PHP have been dropped.

Laravel 11 and 12 as well as PHP 8.2, 8.3, and 8.4 are now supported.

| Laravel | PHP | Branch |
|---|---|---|
|  11+ | 8.2+ | [master](https://github.com/tehwave/laravel-shortcodes/tree/master) |
|  10 and below | 8.1 and below | [1.x](https://github.com/tehwave/laravel-shortcodes/tree/1.x) |

### Incompatible Changes

#### PHP and Laravel Version Requirements

- The minimum PHP version has been increased to 8.2.
- The minimum Laravel version has been increased to 11.

#### Attribute Casting

The attribute casting feature has been introduced.

Cast attributes by defining a `$casts` property on the base shortcode class. This may break existing code that was already using the property.

In addition, to support casting, the base shortcode class implements `HasAttributes` trait from Laravel, which introduces new methods and properties to the base shortcode class.

#### Accessing Attributes as Properties

Attributes can now be accessed directly as properties of the shortcode instance. 

This change may also break existing code that defines a property on the shortcode that conflicts with an attribute. Ensure that there are no naming conflicts between your shortcode properties and attributes.

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