<?php

namespace Voice\OpenApi\Contracts;

interface Serializable
{
    public function toSchema(): array;
}
