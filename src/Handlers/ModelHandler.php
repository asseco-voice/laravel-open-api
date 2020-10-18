<?php

namespace Voice\OpenApi\Parsers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class ModelHandler
{
    protected array $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function parse(string $controller, string $namespace, string $candidate): ?Model
    {
        $model = $this->getModelFromDocBlock($namespace);

        if (class_exists($model)) {
            return new $model;
        }

        $mapping = Config::get('asseco-open-api.controller_model_mapping');

        if (array_key_exists($controller, $mapping)) {
            return new $mapping[$controller];
        }

        if (class_exists($namespace . $candidate)) {
            $class = $namespace . $candidate;
            return new $class;
        }

        return null;
    }

    protected function getModelFromDocBlock(string $namespace): ?string
    {
        if (count($this->tags) === 0) {
            return null;
        }

        $model = $this->tags[0];

        if (!$this->modelNamespaced($model)) {
            return $namespace . $model;
        }

        return $model;
    }

    protected function modelNamespaced($model): bool
    {
        return count(explode('\\', $model)) > 1;
    }

}
