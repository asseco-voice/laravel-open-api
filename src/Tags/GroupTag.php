<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Tags;

class GroupTag extends AbstractTag
{
    protected static function tagName(): string
    {
        return 'group';
    }
}
