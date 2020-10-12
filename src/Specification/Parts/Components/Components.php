<?php

namespace Voice\OpenApi\Specification\Parts\Components;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Extractor;

interface Components extends Serializable
{
    public function generate(Extractor $extractor): void;
}
