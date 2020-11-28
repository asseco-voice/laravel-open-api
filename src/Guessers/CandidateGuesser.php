<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Guessers;

class CandidateGuesser
{
    /**
     * Guess possible model name (candidate) from controller name.
     * @param $controller
     * @return string
     */
    public function __invoke($controller): string
    {
        $split = explode('\\', $controller);
        $controllerName = end($split);

        return str_replace('Controller', '', $controllerName);
    }
}
