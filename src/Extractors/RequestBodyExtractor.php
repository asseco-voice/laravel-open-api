<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Shared\Column;

class RequestBodyExtractor extends AbstractTagExtractor
{
    protected const REQUEST_PARAMETER_TAG_NAME = 'request';

    public function __invoke(DocBlock $methodDocBlock): array
    {
        $methodParameters = $this->getTags($methodDocBlock, self::REQUEST_PARAMETER_TAG_NAME);

        if (!$methodParameters) {
            return [];
        }

        $columns = [];
        foreach ($methodParameters as $methodParameter) {

            $exploded = explode(PHP_EOL, $methodParameter);

            foreach ($exploded as $item) {
                $split = explode(' ', $item, 4);
                $count = count($split);

                if ($count < 2) {
                    throw new OpenApiException("Wrong number of request parameters provided");
                }

                $name = $split[0];
                $type = $split[1];
                $required = ($count >= 3) ? $this->parseRequired($split[2]) : true;
                $description = ($count >= 4) ? $split[3] : '';

                $columns[] = new Column($name, $type, $required, $description);
            }
        }

        return $columns;
    }

    protected function parseRequired(string $required): bool
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
