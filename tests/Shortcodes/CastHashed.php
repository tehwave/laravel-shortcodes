<?php

namespace tehwave\Shortcodes\Tests\Shortcodes;

use tehwave\Shortcodes\Shortcode;

class CastHashed extends Shortcode
{
    protected $casts = [
        'testHashed' => 'hashed',
    ];

    /**
     * The code to run when the Shortcode is being compiled.
     *
     * You may return a string from here, that will then
     * be inserted into the content being compiled.
     */
    public function handle(): ?string
    {
        dump($this->testHashed);

        return is_string($this->testHashed) ? 'true' : 'false';
    }
}
