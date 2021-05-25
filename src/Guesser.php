<?php

declare(strict_types=1);

namespace Asseco\OpenApi;

use Illuminate\Support\Str;

class Guesser
{
    public static function modelNamespace(string $controller): string
    {
        $packageNamespace = Str::before($controller, 'App');

        if (empty($packageNamespace)) {
            return config('asseco-open-api.model_namespace');
        }

        return $packageNamespace . config('asseco-open-api.model_namespace');
    }

    public static function modelName($controller): string
    {
        $controllerName = Str::afterLast($controller, '\\');

        return str_replace('Controller', '', $controllerName);
    }

    public static function groupName(string $candidate): string
    {
        // Split words by uppercase letter.
        $split = preg_split('/(?=[A-Z])/', $candidate);
        // Unsetting first element because it is always empty.
        unset($split[0]);

        return Str::plural(implode(' ', $split));
    }
}
