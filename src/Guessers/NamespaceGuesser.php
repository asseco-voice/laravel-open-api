<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Guessers;

class NamespaceGuesser
{
    protected const SPLIT_BY = 'App';

    /**
     * Guess possible namespace from given controller.
     *
     * @param string $controller
     * @return string
     */
    public function __invoke(string $controller): string
    {
        $split = preg_split('|' . self::SPLIT_BY . '|', $controller);

        if (count($split) < 2) {
            return config('asseco-open-api.model_namespace');
        }

        return $split[0] . config('asseco-open-api.model_namespace');
    }
}
