<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Tags;

class ResponseAppendTag extends AbstractTag
{
    protected static function tagName(): string
    {
        return 'responseAppend';
    }
}
