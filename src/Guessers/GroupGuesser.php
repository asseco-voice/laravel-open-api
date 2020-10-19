<?php

declare(strict_types=1);

namespace Voice\OpenApi\Guessers;

use Illuminate\Support\Str;

class GroupGuesser
{
    /**
     * Guess possible group name from given candidate
     *
     * @param string $candidate
     * @return string
     */
    public function __invoke(string $candidate): string
    {
        // Split words by uppercase letter.
        $split = preg_split('/(?=[A-Z])/', $candidate);
        // Unsetting first element because it is always empty.
        unset($split[0]);

        return Str::plural(implode(' ', $split));
    }
}
