<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;
use Voice\OpenApi\Guessers\GroupGuesser;

class GroupExtractor extends AbstractTagExtractor
{
    protected const GROUP_TAG_NAME = 'group';

    public function __invoke(DocBlock $methodDocBlock, DocBlock $controllerDocBlock, string $candidate): array
    {
        $methodGroups = $this->getTags($methodDocBlock, self::GROUP_TAG_NAME);
        $controllerGroups = $this->getTags($controllerDocBlock, self::GROUP_TAG_NAME);

        return $methodGroups ?: $controllerGroups ?: [(new GroupGuesser())($candidate)];
    }
}
