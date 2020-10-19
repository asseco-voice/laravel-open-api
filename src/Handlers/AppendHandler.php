<?php

namespace Voice\OpenApi\Handlers;

use Voice\OpenApi\Exceptions\OpenApiException;

class AppendHandler extends AbstractHandler
{
    /**
     * @param string $namespace
     * @return array
     * @throws OpenApiException
     */
    public function handle(string $namespace): array
    {
        $append = [];
        foreach ($this->tags as $tag) {
            $split = explode(' ', $tag);

            if (count($split) != 2) {
                throw new OpenApiException("Append parameters need to have exactly 2 parts.");
            }

            $key = $split[0];
            $model = $split[1];

            if (!$this->modelNamespaced($model)) {
                $model = $namespace . $model;
            }

            if(!class_exists($model)){
                throw new OpenApiException("Appended class doesn't exist.");
            }

            $append[] = [
                'key' => $key,
                'model' => new $model,
            ];
        }

        return $append;
    }
}
