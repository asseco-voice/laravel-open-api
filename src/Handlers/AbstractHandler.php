<?php

namespace Voice\OpenApi\Parsers;

use Voice\OpenApi\Traits\ParsesStringToBoolean;

abstract class AbstractHandler
{
    use ParsesStringToBoolean;

    protected array $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }
}
