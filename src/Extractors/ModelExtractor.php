<?php

namespace Voice\OpenApi\Extractors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Mpociot\Reflection\DocBlock;

class ModelExtractor extends AbstractTagExtractor
{
    protected const MODEL_TAG_NAME = 'model';

    /**
     * Return model class by precedence.
     *
     * 1. Return from config mapping
     * 2. If no mapping is present try to return candidate
     * 3. Return null if nothing is found
     *
     * @param DocBlock $controllerDocBlock
     * @param string $controller
     * @param string $namespace
     * @param string $candidate
     * @return string|null
     */
    public function __invoke(DocBlock $controllerDocBlock, string $controller, string $namespace, string $candidate): ?Model
    {
        $model = $this->getModelFromDocBlock($controllerDocBlock, $namespace);

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

    protected function getModelFromDocBlock(DocBlock $controllerDocBlock, string $namespace): ?string
    {
        $tag = $this->getTags($controllerDocBlock, self::MODEL_TAG_NAME);

        if (count($tag) === 0) {
            return null;
        }

        $model = $tag[0];

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
