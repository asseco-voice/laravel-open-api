<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Exceptions\OpenApiException;
use Asseco\OpenApi\Specification\Shared\Column;

class RequestResponseHandler extends AbstractHandler
{
    /**
     * @return array
     * @throws OpenApiException
     */
    public function handle(): array
    {
        $columns = [];

        foreach ($this->tags as $tag) {
            if (preg_match('/"/', $tag)) {
                preg_match('/"(.*?)"/', $tag, $name);
                $columns[] = $name[1];
                continue;
            }

            $items = explode(PHP_EOL, $tag);
            foreach ($items as $item) {
                [$item, $child] = $this->parseChildAttributes($item);
                $split = explode(' ', $item, 4);
                $count = count($split);

                $this->verifyParameters($count);

                [$name, $type, $required, $description] = $this->parseTag($split, $count);
                $column = new Column($name, $type, $required, $description);
                $column->append($child);

                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param string $item
     * @return array
     */
    private function parseChildAttributes(string $item): array
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
     * @param int $count
     * @throws OpenApiException
     */
    private function verifyParameters(int $count): void
    {
        if ($count < 2) {
            throw new OpenApiException('Wrong number of request parameters provided');
        }
    }

    /**
     * @param bool $split
     * @param int $count
     * @return array
     * @throws OpenApiException
     */
    private function parseTag(array $split, int $count): array
    {
        $name = $split[0];
        $type = $split[1];
        $required = $this->isRequired($count, $split);
        $description = $this->getDescription($count, $split);

        return [$name, $type, $required, $description];
    }

    /**
     * @param int $count
     * @param array $split
     * @return bool
     * @throws OpenApiException
     */
    private function isRequired(int $count, array $split): bool
    {
        return ($count >= 3) ? $this->parseBooleanString($split[2]) : true;
    }

    /**
     * @param int $count
     * @param array $split
     * @return string
     */
    private function getDescription(int $count, array $split): string
    {
        return ($count >= 4) ? $split[3] : '';
    }
}
