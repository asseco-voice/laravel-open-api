<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;

class RequestBodyExtractor extends AbstractCommunicationExtractor
{
    protected const REQUEST_PARAMETER_TAG_NAME = 'request';

    public function __invoke(DocBlock $methodDocBlock): array
    {
        return $this->extract($methodDocBlock, self::REQUEST_PARAMETER_TAG_NAME);
    }
}
