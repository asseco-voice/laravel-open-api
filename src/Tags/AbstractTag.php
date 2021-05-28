<?php

namespace Asseco\OpenApi\Tags;

use Mpociot\Reflection\DocBlock;
use Mpociot\Reflection\DocBlock\Tag;

abstract class AbstractTag
{
    abstract protected static function tagName(): string;

    public static function getFrom(DocBlock $docBlock): array
    {
        return self::getTags($docBlock, static::tagName());
    }

    protected static function getTags(DocBlock $docBlock, string $tagName): array
    {
        $tags = $docBlock->getTagsByName($tagName);

        return self::getTagContent($tags);
    }

    protected static function getTagContent(array $groups): array
    {
        return array_map(function ($group) {
            /**
             * @var Tag $group
             */
            return $group->getContent();
        }, $groups);
    }
}
