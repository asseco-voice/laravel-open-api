<?php

namespace Asseco\OpenApi\Handlers;

use Illuminate\Database\Eloquent\Model;

class ModelHandler extends AbstractHandler
{
    public static function handle($tags, string $controller, string $namespace, string $candidate): ?Model
    {
        $model = self::getModelFromDocBlock($tags, $namespace);

        if (class_exists($model)) {
            return new $model;
        }

        $mapping = config('asseco-open-api.controller_model_mapping');

        if (array_key_exists($controller, $mapping)) {
            return new $mapping[$controller];
        }

        if (class_exists($namespace . $candidate)) {
            $class = $namespace . $candidate;

            return new $class;
        }

        return null;
    }

    protected static function getModelFromDocBlock($tags, string $namespace): ?string
    {
        if (count($tags) === 0) {
            return null;
        }

        $model = $tags[0];

        if (!self::modelNamespaced($model)) {
            return $namespace . $model;
        }

        return $model;
    }
}
