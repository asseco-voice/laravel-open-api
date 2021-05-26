<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Traits\ParsesStringToBoolean;

abstract class AbstractHandler
{
    use ParsesStringToBoolean;

    protected static function modelNamespaced($model): bool
    {
        return count(explode('\\', $model)) > 1;
    }
}
