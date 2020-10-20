<?php

declare(strict_types=1);

namespace Voice\OpenApi\Contracts;

interface Serializable
{
    /**
     * Return OpenApi schema as an array.
     * @return array
     */
    public function toSchema(): array;
}
