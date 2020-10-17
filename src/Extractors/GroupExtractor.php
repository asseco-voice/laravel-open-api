<?php

namespace Voice\OpenApi\Extractors;

use Illuminate\Support\Str;
use Mpociot\Reflection\DocBlock;

class GroupExtractor extends AbstractTagExtractor
{
    protected const GROUP_TAG_NAME = 'group';

    /**
     * Get group tags by precedence.
     *
     * 1. return method groups
     * 2. if they don't exist return controller groups
     * 3. if they don't exist return guessed group
     *
     * @param DocBlock $methodDocBlock
     * @param DocBlock $controllerDocBlock
     * @param string $candidate
     * @return array
     */
    public function __invoke(DocBlock $methodDocBlock, DocBlock $controllerDocBlock, string $candidate): array
    {
        $methodGroups = $this->getTags($methodDocBlock, self::GROUP_TAG_NAME);
        $controllerGroups = $this->getTags($controllerDocBlock, self::GROUP_TAG_NAME);

        return $methodGroups ?: $controllerGroups ?: [$this->guessGroup($candidate)];
    }

    /**
     * Guess the group name from candidate.
     *
     * Given the candidate SomeRandomClass, resulting group will be "Some Random Class"
     *
     * @param string $candidate
     * @return string
     */
    protected function guessGroup(string $candidate): string
    {
        // Split words by uppercase letter.
        $split = preg_split('/(?=[A-Z])/', $candidate);
        // Unsetting first element because it is always empty.
        unset($split[0]);

        return Str::plural(implode(' ', $split));
    }
}
