<?php

namespace tehwave\Shortcodes;

use Exception;
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
     * The shortcode's body content.
     *
     * @var string
     */
    protected $body;

    /**
     * Create a new Shortcode instance.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function __construct(array $attributes = [], ?string $body = null)
    {
        $this->attributes = $attributes;

        $this->body = (string) $body;
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
        $this->attributes = Compiler::resolveAttributes($attributes);

        foreach ((array) $this->attributes as $key => $value) {

            // Cast the attribute if it has a cast.
            if (is_string($key) && $this->hasCast($key)) {
                try {
                    $this->attributes[$key] = $this->castAttribute($key, $value);
                } catch (Exception) {
                    // For whatever reason, we couldn't cast the attribute.
                    $this->attributes[$key] = null;
                }
            }
        }

        $this->body = $body;

        return $this->handle();
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
                ->transform(function (string $class): string {
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
            ->transform(function (string $class) {
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
     * Get the attributes that should be cast.
     *
     * @return array
     */
    public function getCasts()
    {
        // Lowercase the keys because we normalize attribute keys when parsing from regex,
        // and we need the cast keys to match with the attribute keys when casting.
        return array_change_key_case($this->casts, CASE_LOWER);
    }

    /**
     * Determine whether an attribute should be cast to a native type.
     *
     * @param  string  $key
     * @param  array|string|null  $types
     * @return bool
     */
    public function hasCast($key, $types = null)
    {
        if (array_key_exists($key, $this->getCasts())) {
            return $types ? in_array($this->getCastType($key), (array) $types, true) : true;
        }

        return false;
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat ?: app('db')->connection()->getQueryGrammar()->getDateFormat();
    }

    /**
     * Dynamically retrieve attributes on the shortcode.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        if (! $key) {
            return;
        }

        // Key must be lowercase as this is how we store and normalize attributes.
        if (isset($this->attributes[strtolower($key)])) {

            // Any attributes with casts are already casted.
            return $this->attributes[strtolower($key)];
        }

        // Not looking for an attribute.
        return $this->{$key} ?? null;
    }
}
