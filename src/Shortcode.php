<?php

namespace tehwave\Shortcodes;

use Illuminate\Support\Collection;

class Shortcode
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
     * The code to run when the shortcode is being compiled.
     *
     * @return void
     */
    abstract public function execute();

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
