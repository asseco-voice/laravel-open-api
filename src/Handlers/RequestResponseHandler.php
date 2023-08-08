<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Exceptions\OpenApiException;
use Asseco\OpenApi\Specification\Shared\Column;

class RequestResponseHandler extends AbstractHandler
{
    /**
     * @param  array  $tags
     * @return array
     *
     * @throws OpenApiException
     */
    public static function handle(array $tags): array
    {
        $columns = [];

        foreach ($tags as $tag) {
            $matchInQuotes = self::matchInQuotes($tag);

            if (!empty($matchInQuotes)) {
                $columns[] = $matchInQuotes[1];
                continue;
            }

            $items = explode(PHP_EOL, $tag);

            foreach ($items as $item) {
                [$item, $child] = self::parseChildAttributes($item);

                $split = explode(' ', $item, 4);
                $count = count($split);

                self::verifyParameters($count);

                [$name, $type, $required, $description] = self::parseTag($split, $count);

                $column = new Column($name, $type, $required, $description);
                $column->append($child);

                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Covering the case in the response when a simple string should be returned.
     *
     * @param  $tag
     * @return false|int
     */
    protected static function matchInQuotes($tag)
    {
        preg_match('/"(.*?)"/', $tag, $matchInQuotes);

        return $matchInQuotes;
    }

    /**
     * @param  string  $item
     * @return array
     */
    protected static function parseChildAttributes(string $item): array
    {
        preg_match("|\[(.*)]|", $item, $arrayAttribute);

        $child = null;

        if (count($arrayAttribute) === 2) {
            $item = str_replace($arrayAttribute[0], '', $item);
            $child = new Column('', $arrayAttribute[1], true);
        }

        return [$item, $child];
    }

    /**
     * @param  int  $count
     *
     * @throws OpenApiException
     */
    protected static function verifyParameters(int $count): void
    {
        if ($count < 2) {
            throw new OpenApiException('Wrong number of request parameters provided');
        }
    }

    /**
     * @param  bool  $split
     * @param  int  $count
     * @return array
     *
     * @throws OpenApiException
     */
    protected static function parseTag(array $split, int $count): array
    {
        $name = $split[0];
        $type = $split[1];
        $required = self::isRequired($count, $split);
        $description = self::getDescription($count, $split);

        return [$name, $type, $required, $description];
    }

    /**
     * @param  int  $count
     * @param  array  $split
     * @return bool
     *
     * @throws OpenApiException
     */
    protected static function isRequired(int $count, array $split): bool
    {
        return ($count >= 3) ? self::parseBooleanString($split[2]) : true;
    }

    /**
     * @param  int  $count
     * @param  array  $split
     * @return string
     */
    protected static function getDescription(int $count, array $split): string
    {
        return ($count >= 4) ? $split[3] : '';
    }
}
