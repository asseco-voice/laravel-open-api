<?php

namespace Voice\OpenApi\Traits;

trait MergesArrays
{
    /**
     * @param array $input
     * @param string $key
     * @return array
     */
    public function nestUnder(array $input, string $key): array
    {
        $schema = [];
        $schema[$key] = [];

        foreach ($input as $item) {
            foreach ($item as $innerKey => $content) {
                $schema[$key][$innerKey] = $content;
            }
        }

        return $schema;
    }
}
