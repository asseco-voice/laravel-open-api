<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Tags;

class ModelTag extends AbstractTag
{
    protected static function tagName(): string
    {
        return 'model';
    }
}
