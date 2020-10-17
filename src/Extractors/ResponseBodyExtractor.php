<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;

class ResponseBodyExtractor extends AbstractCommunicationExtractor
{
    protected const RESPONSE_PARAMETER_TAG_NAME = 'response';

    public function __invoke(DocBlock $methodDocBlock): array
    {
        return $this->extract($methodDocBlock, self::RESPONSE_PARAMETER_TAG_NAME);
    }
}
