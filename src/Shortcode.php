<?php

namespace tehwave\Shortcodes;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

abstract class Shortcode
{
    /**
     * The cache of a list of Shortcode classes.
     *
     * @return \Illuminate\Support\Collection
     */
    protected static $classesCache;

    /**
     * The cache of a list of namespaced Shortcode classes.
     *
     * @return \Illuminate\Support\Collection
     */
    protected static $namespacedClassesCache;

    /**
     * The tag to match in content.
     *
     * @var string
     */
    protected $tag;

    /**
     * The shortcode's attributes.
     *
     * @var array|null
     */
    protected $attributes = null;

    /**
     * The shortcode's body content.
     *
     * @var string
     */
    protected $body;

    /**
     * Create a new Shortcode instance.
     *
     * @param array|null       $attributes
     * @param string|null      $body
     */
    public function __construct(array $attributes = null, string $body = null)
    {
        $this->attributes = $attributes;

        $this->body = $body;
    }

    /**
     * Retrieve the tag to match in content.
     *
     * Should the tag not be pre-defined, we will resolve
     * the tag from the class name into snake_case.
     *
     * @return string
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
     *
     * @return string|null
     */
    abstract public function handle(): ?string;

    /**
     * This method runs when the shortcode has been parsed from content.
     *
     * @param  array  $matches
     *
     * @return string|null
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

        $this->body = $body;

        return $this->handle();
    }

    /**
     * Retrieve all of the Shortcode classes.
     *
     * @return \Illuminate\Support\Collection
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
     *
     * @return \Illuminate\Support\Collection
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
     *
     * @return \Illuminate\Support\Collection
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
     *
     * @return void
     */
    public static function clearCache(): void
    {
        static::$classesCache = null;
        static::$namespacedClassesCache = null;
    }

    /**
     * A shorthand method for getNamespacedClasses.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all(): Collection
    {
        return static::getInstantiatedClasses();
    }

    /**
     * A shorthand method for compile method on Compiler.
     *
     * @param  string $content
     *
     * @return string
     */
    public static function compile(string $content, Collection $shortcodes = null): string
    {
        return Compiler::compile($content, $shortcodes);
    }
}
