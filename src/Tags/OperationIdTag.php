<?php

namespace Asseco\OpenApi\Tags;

class OperationIdTag extends AbstractTag
{
    protected static function tagName(): string
    {
        return 'operationId';
    }
}
