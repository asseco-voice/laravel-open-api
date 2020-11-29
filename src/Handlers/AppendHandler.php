<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Exceptions\OpenApiException;

class AppendHandler extends AbstractHandler
{
    public function handle(string $namespace): array
    {
        return array_map(function ($tag) use ($namespace) {
            return $this->parseTag($tag, $namespace);
        }, $this->tags);
    }

    /**
     * @param $tag
     * @param $namespace
     * @return array
     * @throws OpenApiException
     */
    protected function parseTag($tag, $namespace): array
    {
        $split = explode(' ', $tag);

        $this->verifyParameters(count($split));

        $key = $split[0];
        $model = $split[1];

        if (!$this->modelNamespaced($model)) {
            $model = $namespace . $model;
        }

        $this->verifyModelExists($model);

        return [
            'key'   => $key,
            'model' => new $model,
        ];
    }

    /**
     * @param array $count
     * @throws OpenApiException
     */
    protected function verifyParameters(int $count): void
    {
        if ($count != 2) {
            throw new OpenApiException('Append parameters need to have exactly 2 parts.');
        }
    }

    /**
     * @param string $model
     * @throws OpenApiException
     */
    protected function verifyModelExists(string $model): void
    {
        if (!class_exists($model)) {
            throw new OpenApiException("Appended class doesn't exist.");
        }
    }
}
