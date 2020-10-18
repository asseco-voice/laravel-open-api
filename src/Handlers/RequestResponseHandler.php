<?php

namespace Voice\OpenApi\Parsers;

use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Shared\Column;

class RequestResponseHandler
{
    protected array $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function parse(): array
    {
        if (!$this->tags) {
            return [];
        }

        $columns = [];
        foreach ($this->tags as $tag) {

            $exploded = explode(PHP_EOL, $tag);

            foreach ($exploded as $item) {
                $split = explode(' ', $item, 4);
                $count = count($split);

                if ($count < 2) {
                    throw new OpenApiException("Wrong number of request parameters provided");
                }

                $name = $split[0];
                $type = $split[1];
                $required = ($count >= 3) ? $this->parseBooleanString($split[2]) : true;
                $description = ($count >= 4) ? $split[3] : '';

                $columns[] = new Column($name, $type, $required, $description);
            }
        }

        return $columns;
    }

    /**
     * @param string $required
     * @return bool
     * @throws OpenApiException
     */
    protected function parseBooleanString(string $required): bool
    {
        if ($required === 'true' || $required === '1') {
            return true;
        } elseif ($required === 'false' || $required === '0') {
            return false;
        } else {
            throw new OpenApiException("Required property must be boolean.");
        }
    }
}
