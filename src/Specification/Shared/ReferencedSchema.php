<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Shared;

class ReferencedSchema extends Schema
{
    public function toSchema(): array
    {
        $referencedModel = ['$ref' => "#/components/schemas/$this->name"];

        return $this->multiple ? $this->generateMultipleSchema($referencedModel) : $referencedModel;
    }
}
