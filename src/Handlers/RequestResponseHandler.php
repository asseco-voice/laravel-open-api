<?php

namespace Voice\OpenApi\Handlers;

use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Shared\Column;

class RequestResponseHandler extends AbstractHandler
{
    public function handle(): array
    {
        if (!$this->tags) {
            return [];
        }

        $columns = [];
        foreach ($this->tags as $tag) {

            $exploded = explode(PHP_EOL, $tag);

            foreach ($exploded as $item) {

                preg_match("|\[(.*)]|", $item, $arrayAttribute);

                $child = null;
                if (count($arrayAttribute) === 2) {
                    $item = str_replace($arrayAttribute[0], '', $item);
                    $child = new Column('', $arrayAttribute[1], true);
                }

                $split = explode(' ', $item, 4);
                $count = count($split);

                if ($count < 2) {
                    throw new OpenApiException("Wrong number of request parameters provided");
                }

                $name = $split[0];
                $type = $split[1];

                try {
                    $required = ($count >= 3) ? $this->parseBooleanString($split[2]) : true;
                } catch (OpenApiException $e) {
                    throw new OpenApiException("Wrong parameters provided for $tag");
                }

                $description = ($count >= 4) ? $split[3] : '';

                $column = new Column($name, $type, $required, $description);

                if ($child) {
                    $column->append($child);
                }

                $columns[] = $column;
            }
        }

        return $columns;
    }
}
