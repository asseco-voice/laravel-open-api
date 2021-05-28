<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Tags;

class MultipleTag extends AbstractTag
{
    protected static function tagName(): string
    {
        return 'multiple';
    }
}
