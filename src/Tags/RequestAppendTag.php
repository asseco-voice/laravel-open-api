<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Tags;

class RequestAppendTag extends AbstractTag
{
    protected static function tagName(): string
    {
        return 'requestAppend';
    }
}
