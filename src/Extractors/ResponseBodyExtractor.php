<?php

namespace Voice\OpenApi\Extractors;

use Mpociot\Reflection\DocBlock;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Shared\Column;

class ResponseBodyExtractor extends AbstractTagExtractor
{
    protected const RESPONSE_PARAMETER_TAG_NAME = 'response';

    /**
     * Get group tags by precedence.
     *
     * 1. return method groups
     * 2. if they don't exist return controller groups
     * 3. if they don't exist return guessed group
     *
     * @param DocBlock $methodDocBlock
     * @param array $pathParameters
     * @return array
     * @throws OpenApiException
     */
    public function __invoke(DocBlock $methodDocBlock): array
    {
        $responseTags = $this->getTags($methodDocBlock, self::RESPONSE_PARAMETER_TAG_NAME);

        if (!$responseTags) {
            return [];
        }

        $columns = [];
        foreach ($responseTags as $methodParameter) {

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
