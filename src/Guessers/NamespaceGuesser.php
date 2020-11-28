<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Guessers;

class NamespaceGuesser
{
    protected const DEFAULT_NAMESPACE = 'App';

    /**
     * Guess possible namespace from given controller.
     *
     * @param string $controller
     * @return string
     */
    public function __invoke(string $controller): string
    {
        $split = preg_split('|' . self::DEFAULT_NAMESPACE . '|', $controller);

        if (count($split) < 2) {
            return self::DEFAULT_NAMESPACE . '\\';
        }

        return $split[0] . self::DEFAULT_NAMESPACE . '\\';
    }
}
