<?php

namespace Voice\OpenApi\Guessers;

class CandidateGuesser
{
    public function __invoke($controller)
    {
        $split = explode('\\', $controller);
        $controllerName = end($split);

        return str_replace('Controller', '', $controllerName);
    }
}
