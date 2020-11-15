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
class NameGenerator
{
    public const FLAG_FEMALE = 'f';
    public const FLAG_MALE = 'm';

    private static array $namesFemale = [
        'Andrea', 'Angelika', 'Anja', 'Anna', 'Birgit', 'Christina', 'Gabriele', 'Hannah', 'Heike', 'Julia',
        'Katrin', 'Lara', 'Laura', 'Lea', 'Lena', 'Lisa', 'Martina', 'Melanie', 'Michelle', 'Nadine',
        'Nicole', 'Petra', 'Sabine', 'Sabrina', 'Sandra', 'Sarah', 'Stefanie', 'Susanne', 'Ute',
    ];

    private static array $namesMale = [
        'Alexander', 'Andreas', 'Christian', 'Daniel', 'Dennis', 'Finn', 'Frank', 'Jan', 'Jannik', 'Jonas',
        'Jörg', 'Jürgen', 'Klaus', 'Leon', 'Luca', 'Lukas', 'Martin', 'Michael', 'Niklas', 'Otto', 'Paul',
        'Peter', 'Sebastian', 'Stefan', 'Thomas', 'Tim', 'Tom', 'Uwe', 'Walter',
    ];

    private int $seed;

    private AbstractRand $randomGenerator;

    /** @var array<int, array<string>> */
    private static array $usedNames = [];

    public function __construct(int $seed, string $specimen = '')
    {
        $this->seed = $seed;

        $this->randomGenerator = new XorShiftRand($seed.$specimen);
    }

    /**
     * Generate the next sequential name. Types should be separated by comma.
     * If possible (= not already used up) the generated names will be unique
     * across the same seeded bucket.
     */
    public function next(string $types): string
    {
        $name = $this->randomGenerator->arrayRandValue(
            $this->getAvailableNames($types)
        );

        // Try to keep names unique across same seed
        $this->markAsUsed($name);

        return $name;
    }

    /**
     * Reset usage statics for a all entries or a single seed bucket.
     */
    public static function reset(int $seed = null): void
    {
        if (null === $seed) {
            self::$usedNames = [];

            return;
        }

        unset(self::$usedNames[$seed]);
    }

    private function getAvailableNames(string $types): array
    {
        $typeEntities = array_map(
            static fn (string $part): string => strtolower(trim($part)),
            explode(',', $types)
        );

        $names = array_merge(
            \in_array(self::FLAG_FEMALE, $typeEntities, true) ? self::$namesFemale : [],
            \in_array(self::FLAG_MALE, $typeEntities, true) ? self::$namesMale : [],
        );

        if (empty($names)) {
            $names = ['?'];
        }

        $remainingNames = array_diff($names, self::$usedNames[$this->seed] ?? []);

        // If no names are remaining throw all candidates in the mix again
        return empty($remainingNames) ? $names : $remainingNames;
    }

    private function markAsUsed(string $name): void
    {
        self::$usedNames[$this->seed] = array_merge(
            self::$usedNames[$this->seed] ?? [],
            [$name]
        );
    }
}
