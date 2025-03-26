<?php

namespace tehwave\Shortcodes\Tests\Shortcodes;

use Illuminate\Support\Collection;
use tehwave\Shortcodes\Shortcode;

class CastCollection extends Shortcode
{
    protected $casts = [
        'testCollection' => 'collection',
    ];

    /**
     * The code to run when the Shortcode is being compiled.
     *
     * You may return a string from here, that will then
     * be inserted into the content being compiled.
     */
    public function handle(): ?string
    {
        return $this->testCollection instanceof Collection ? 'true' : 'false';
    }
}
