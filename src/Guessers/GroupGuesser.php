<?php

namespace Voice\OpenApi\Guessers;

use Illuminate\Support\Str;

class GroupGuesser
{
    public function __invoke(string $candidate)
    {
        // Split words by uppercase letter.
        $split = preg_split('/(?=[A-Z])/', $candidate);
        // Unsetting first element because it is always empty.
        unset($split[0]);

        return Str::plural(implode(' ', $split));
    }
}
