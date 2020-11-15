<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom;

use Savvot\Random\AbstractRand;
use Savvot\Random\XorShiftRand;

/**
 * @internal
 */
class IntegerGenerator
{
    private AbstractRand $randomGenerator;

    public function __construct(int $seed, string $specimen = '')
    {
        $this->randomGenerator = new XorShiftRand($seed.$specimen);
    }

    /**
     * Generate the next sequential random integer.
     */
    public function next(int $min, int $max): int
    {
        return $this->randomGenerator->random($min, $max);
    }
}
