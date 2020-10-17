<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;

class ExceptExtractor extends AbstractTagExtractor
{
    protected const EXCEPT_TAG_NAME = 'except';

    public function __invoke(DocBlock $methodDocBlock): array
    {
        $responseTags = $this->getTags($methodDocBlock, self::EXCEPT_TAG_NAME);

        return $responseTags ? explode(' ', $responseTags[0]) : [];
    }
}
