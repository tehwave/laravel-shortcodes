<?php

namespace tehwave\Shortcodes;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

abstract class Shortcode
{
    /**
     * The cache of a list of Shortcode classes.
     *
     * @var array
     */
    protected static $classesCache = [];

    /**
     * The cache of a list of namespaced Shortcode classes.
     *
     * @var array
     */
    protected static $namespacedClassesCache = [];

    /**
     * The tag to match in content.
     *
     * @var string
     */
    protected static $tag;

    /**
     * The shortcode's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The shortcode's body content.
     *
     * @var string
     */
    protected $body;

    /**
     * Create a new Shortcode instance.
     *
     * @param array       $attributes
     * @param string|null $body
     */
    public function __construct(array $attributes = [], string $body = null)
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
        if (! isset(static::$tag)) {
            $className = class_basename($this);

            $snakedClassName = Str::snake($className);

            static::$tag = $snakedClassName;
        }

        return static::$tag;
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
     * A shorthand method for getNamespacedClasses.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all(): Collection
    {
        return static::getNamespacedClasses();
    }
}
