<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;

class MultipleExtractor extends AbstractTagExtractor
{
    protected const MULTIPLE_TAG_NAME = 'multiple';

    public function __invoke(DocBlock $methodDocBlock): bool
    {
        return !empty($this->getTags($methodDocBlock, self::MULTIPLE_TAG_NAME));
    }
}
