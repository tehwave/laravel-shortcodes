<?php

namespace tehwave\Shortcodes\Tests\Shortcodes;

use tehwave\Shortcodes\Shortcode;

class CastBoolean extends Shortcode
{
    protected $casts = [
        'test-boolean' => 'boolean',
    ];

    /**
     * The code to run when the Shortcode is being compiled.
     *
     * You may return a string from here, that will then
     * be inserted into the content being compiled.
     */
    public function handle(): ?string
    {
        return $this->testBoolean ? 'true' : 'false';
    }
}
