<?php

namespace Voice\OpenApi\Specification\Parts\Components\Models;

use Voice\OpenApi\Contracts\Serializable;

interface Component extends Serializable
{
    public function generateProperties(array $modelColumns);
}
