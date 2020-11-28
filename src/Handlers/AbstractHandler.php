<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Traits\ParsesStringToBoolean;

abstract class AbstractHandler
{
    use ParsesStringToBoolean;

    protected array $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    protected function modelNamespaced($model): bool
    {
        return count(explode('\\', $model)) > 1;
    }
}
