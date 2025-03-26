<?php

namespace tehwave\Shortcodes\Tests\Shortcodes;

use tehwave\Shortcodes\Shortcode;

class CastString extends Shortcode
{
    protected $casts = [
        'testString' => 'string',
    ];

    /**
     * The code to run when the Shortcode is being compiled.
     *
     * You may return a string from here, that will then
     * be inserted into the content being compiled.
     */
    public function handle(): ?string
    {
        return is_string($this->testString) ? 'true' : 'false';
    }
}
