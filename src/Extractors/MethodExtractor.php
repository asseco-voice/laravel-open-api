<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;

class MethodExtractor extends AbstractTagExtractor
{
    public function __invoke(DocBlock $methodDocBlock, array $groups): array
    {
        return [
            'summary'     => $methodDocBlock->getShortDescription(),
            'description' => $methodDocBlock->getLongDescription()->getContents(),
            'tags'        => $groups,
        ];
    }
}
