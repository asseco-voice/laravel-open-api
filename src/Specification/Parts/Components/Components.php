<?php

namespace Voice\OpenApi\Specification\Parts\Components;

use Voice\OpenApi\Contracts\Serializable;

interface Components extends Serializable
{
    public function generate(string $name, array $modelColumns): void;
}
