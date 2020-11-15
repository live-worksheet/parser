<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\ExpressionLanguage\SequentialRandom;

use LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom\NameGenerator;
use PHPUnit\Framework\TestCase;

class NameGeneratorTest extends TestCase
{
    public function testGeneratesRandomNames(): void
    {
        $generator = new NameGenerator(1);

        // female
        self::assertEquals('Sabine', $generator->next('f'));
        self::assertEquals('Katrin', $generator->next('f'));

        // male
        self::assertEquals('Paul', $generator->next('m'));
        self::assertEquals('Martin', $generator->next('m'));

        // mixed
        self::assertEquals('Sandra', $generator->next('f,m'));
        self::assertEquals('Alexander', $generator->next('f,m'));

        // invalid
        self::assertEquals('?', $generator->next(''));
        self::assertEquals('?', $generator->next('abc'));
    }

    public function testGeneratesSequentialRandomNames(): void
    {
        $generator1 = new NameGenerator(123, 'foo');
        $generator2 = new NameGenerator(123, 'foo');

        $names = [];

        for ($i = 0; $i < 60; ++$i) {
            $names[] = $generator1->next('f,m');
        }

        NameGenerator::reset(123);

        foreach ($names as $name) {
            self::assertEquals($name, $generator2->next('f,m'));
        }
    }

    public function testReset(): void
    {
        $value1 = (new NameGenerator(10))->next('f');
        $value2 = (new NameGenerator(20))->next('f');

        // Only reset seed '10'
        NameGenerator::reset(10);

        self::assertEquals($value1, (new NameGenerator(10))->next('f'));
        self::assertNotEquals($value2, (new NameGenerator(20))->next('f'));

        // Reset all
        NameGenerator::reset();

        self::assertEquals($value2, (new NameGenerator(20))->next('f'));
    }

    public function testNamesAreUniqueAcrossSeed(): void
    {
        $generator1 = new NameGenerator(3, 'foo');
        $generator2 = new NameGenerator(3, 'bar');

        $names = [];

        for ($i = 0; $i < 29; ++$i) {
            $name = $generator1->next('f,m');
            self::assertNotContains($name, $names);
            $names[] = $name;

            $name = $generator2->next('f,m');
            self::assertNotContains($name, $names);
            $names[] = $name;
        }
    }
}
