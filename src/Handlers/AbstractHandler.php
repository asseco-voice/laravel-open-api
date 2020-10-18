<?php

namespace Voice\OpenApi\Parsers;

abstract class AbstractHandler
{
    protected array $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }
}
