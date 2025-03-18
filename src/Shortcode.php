<?php

namespace tehwave\Shortcodes;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class Shortcode
{
    use HasAttributes;

    /**
     * The cache of a list of Shortcode classes.
     *
     * @var Collection|null
     */
    protected static $classesCache;

    /**
     * The cache of a list of namespaced Shortcode classes.
     *
     * @var Collection|null
     */
    protected static $namespacedClassesCache;

    /**
     * The tag to match in content.
     *
     * @var string
     */
    protected $tag;

    /**
     * Optional closing tag to match in content.
     *
     * @var string
     */
    protected $closingTag;

    /**
     * The shortcode's body content.
     *
     * @var string
     */
    protected $body;

    /**
     * Create a new Shortcode instance.
     */
    public function __construct(?array $attributes = null, ?string $body = null)
    {
        $this->attributes = $attributes;

        $this->body = $body;
    }

    /**
     * Retrieve the tag to match in content.
     *
     * Should the tag not be pre-defined, we will resolve
     * the tag from the class name into snake_case.
     */
    public function getTag(): string
    {
        if (! isset($this->tag)) {
            $className = class_basename($this);

            $snakedClassName = Str::snake($className);

            $this->tag = $snakedClassName;
        }

        return $this->tag;
    }

    /**
     * The code to run when the Shortcode is being compiled.
     *
     * You may return a string from here, that will then
     * be inserted into the content being compiled.
     */
    abstract public function handle(): ?string;

    /**
     * This method runs when the shortcode has been parsed from content.
     */
    public function dispatch(array $matches): ?string
    {
        // Let's make these matches human readable.
        [$shortcode, $prefix, $tag, $attributes, $tagClose, $body, $suffix] = $matches;

        // Allows escaping shortcodes by wrapping in square brackets.
        if ($prefix === '[' && $suffix === ']') {
            return substr($shortcode, 1, -1);
        }

        // Set up our inputs and run our handle.
        $this->attributes = $this->setAttributeDefaults(
            Compiler::resolveAttributes($attributes)
        );

        $this->body = $body;

        return $this->handle();
    }

    /**
     * Set defaults for attributes without a value that should be cast to boolean.
     * e.g. [shortcode boolean-attribute string="value"]
     *
     * @return void
     */
    protected function setAttributeDefaults($attributes)
    {
        $attributes = [
            ...$this->getCastAttributeDefaults(),
            ...collect($attributes ?? [])
                ->mapWithKeys(function ($value, $key) {
                    if (array_key_exists($value, $this->casts) && in_array($this->casts[$value], ['boolean', 'bool'])) {
                        return [$value => true];
                    }

                    return [$key => $value];
                })
                ->toArray(),
        ];

        return empty($attributes) ? null : $attributes;
    }

    /**
     * Retrieve the default values for attributes that should be cast to boolean.
     */
    public function getCastAttributeDefaults(): array
    {
        return collect($this->casts)
            ->filter(function ($value) {
                return in_array($value, ['boolean', 'bool']);
            })
            ->map(function () {
                return false;
            })
            ->toArray();
    }

    /**
     * Retrieve all of the Shortcode classes.
     */
    public static function getClasses(): Collection
    {
        if (! isset(static::$classesCache)) {
            $directory = app()->path('Shortcodes');

            $classes = collect(scandir($directory))
                ->diff(['..', '.'])
                ->values();

            static::$classesCache = $classes;
        }

        return static::$classesCache;
    }

    /**
     * Retrieve all of the Shortcode classes in namespace.
     */
    public static function getNamespacedClasses(): Collection
    {
        if (! isset(static::$namespacedClassesCache)) {
            $namespacedClasses = static::getClasses()
                ->transform(function ($class) {
                    return sprintf(
                        '%sShortcodes\%s',
                        app()->getNamespace(),
                        rtrim($class, '.php')
                    );
                });

            static::$namespacedClassesCache = $namespacedClasses;
        }

        return static::$namespacedClassesCache;
    }

    /**
     * Retrieve all of the Shortcode classes as instances.
     */
    public static function getInstantiatedClasses(): Collection
    {
        return self::getNamespacedClasses()
            ->transform(function ($class) {
                return new $class;
            });
    }

    /**
     * Clears the classes cache.
     */
    public static function clearCache(): void
    {
        static::$classesCache = null;
        static::$namespacedClassesCache = null;
    }

    /**
     * A shorthand method for getNamespacedClasses.
     */
    public static function all(): Collection
    {
        return static::getInstantiatedClasses();
    }

    /**
     * A shorthand method for compile method on Compiler.
     */
    public static function compile(string $content, ?Collection $shortcodes = null): string
    {
        return Compiler::compile($content, $shortcodes);
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        return [];
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return $this->casts;
    }

    /**
     * Get an attribute from the class.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        $key = $key === Str::camel($key) && ! array_key_exists($key, $this->casts)
            ? Str::snake($key, '-')
            : $key;

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value
        if (array_key_exists($key, $this->attributes ?? []) ||
            array_key_exists($key, $this->casts) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        return null;
    }

    /**
     * Dynamically retrieve attributes on the class.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }
}
