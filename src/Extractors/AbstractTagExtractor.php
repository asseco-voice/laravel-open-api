<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;
use Mpociot\Reflection\DocBlock\Tag;

abstract class AbstractTagExtractor
{
    protected function getTags(DocBlock $docBlock, string $tagName): array
    {
        $methodGroups = $docBlock->getTagsByName($tagName);

        return $this->getTagContent($methodGroups);
    }

    protected function getTagContent(array $groups): array
    {
        return array_map(function ($group) {
            /**
             * @var Tag $group
             */
            return $group->getContent();
        }, $groups);
    }
}
