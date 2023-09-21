<?php

namespace tehwave\Shortcodes\Tests\Shortcodes;

use tehwave\Shortcodes\Shortcode;

class CastDate extends Shortcode
{
    protected $casts = [
        'test-date' => 'date',
    ];

    /**
     * The code to run when the Shortcode is being compiled.
     *
     * You may return a string from here, that will then
     * be inserted into the content being compiled.
     *
     * @return string|null
     */
    public function handle(): ?string
    {
        return (string) $this->testDate->timestamp;
    }
}
