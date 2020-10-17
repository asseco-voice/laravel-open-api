<?php

namespace Voice\OpenApi\Guessers;

class NamespaceGuesser
{
    protected const DEFAULT_NAMESPACE = 'App';

    public function __invoke(string $controller)
    {
        $split = preg_split('|' . self::DEFAULT_NAMESPACE . '|', $controller);

        if (count($split) < 2) {
            return self::DEFAULT_NAMESPACE . '\\';
        }

        return $split[0] . self::DEFAULT_NAMESPACE . '\\';
    }
}
