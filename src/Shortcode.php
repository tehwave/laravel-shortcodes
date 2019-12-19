<?php

namespace tehwave\Shortcodes;

class Shortcode
{
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
}
