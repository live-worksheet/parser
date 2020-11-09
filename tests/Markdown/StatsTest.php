<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown;

use LiveWorksheet\Parser\Markdown\Stats;
use PHPUnit\Framework\TestCase;

class StatsTest extends TestCase
{
    public function testHitAndEvaluate(): void
    {
        $stats = new Stats();

        self::assertEmpty($stats->getVariablesCounts());
        self::assertEmpty($stats->getVariablePlaceholderCounts());
        self::assertEmpty($stats->getResourcesCounts());

        $stats->hitVariable('Foo');
        $stats->hitVariable('Foo');
        $stats->hitVariable('Bar', false);

        $stats->hitVariablePlaceholder('FooPlaceholder');
        $stats->hitVariablePlaceholder('BarPlaceholder', false);
        $stats->hitVariablePlaceholder('BarPlaceholder', false);

        $stats->hitResourcePath('some/path');
        $stats->hitResourcePath('other/path');
        $stats->hitResourcePath('other/path');
        $stats->hitResourcePath('invalid/path', false);

        self::assertEquals(
            [
                'Foo' => 2,
            ],
            $stats->getVariablesCounts(),
            'variables'
        );

        self::assertEquals(
            [
                'Bar' => 1,
            ],
            $stats->getVariablesCounts(false),
            'invalid variables'
        );

        self::assertEquals([
            'FooPlaceholder' => 1,
        ],
            $stats->getVariablePlaceholderCounts(),
            'placeholders'
        );

        self::assertEquals([
            'BarPlaceholder' => 2,
        ],
            $stats->getVariablePlaceholderCounts(false),
            'invalid placeholders'
        );

        self::assertEquals([
            'some/path' => 1,
            'other/path' => 2,
        ],
            $stats->getResourcesCounts(),
            'resource paths'
        );

        self::assertEquals([
            'invalid/path' => 1,
        ],
            $stats->getResourcesCounts(false),
            'invalid resource paths'
        );
    }

    public function testThrowsIfVariableIsHitAsInvalidAfterBeingHitAsValid(): void
    {
        $stats = new Stats();

        $stats->hitVariable('Foo');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Identifier 'Foo' was already registered being valid.");

        $stats->hitVariable('Foo', false);
    }

    public function testThrowsIfVariableIsHitAsValidAfterBeingHitAsInvalid(): void
    {
        $stats = new Stats();

        $stats->hitVariable('Foo', false);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Identifier 'Foo' was already registered being invalid.");

        $stats->hitVariable('Foo');
    }

    public function testThrowsIfVariablePlaceholderIsHitAsInvalidAfterBeingHitAsValid(): void
    {
        $stats = new Stats();

        $stats->hitVariablePlaceholder('FooPlaceholder');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Identifier 'FooPlaceholder' was already registered being valid.");

        $stats->hitVariablePlaceholder('FooPlaceholder', false);
    }

    public function testThrowsIfVariablePlaceholderIsHitAsValidAfterBeingHitAsInvalid(): void
    {
        $stats = new Stats();

        $stats->hitVariablePlaceholder('FooPlaceholder', false);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Identifier 'FooPlaceholder' was already registered being invalid.");

        $stats->hitVariablePlaceholder('FooPlaceholder');
    }

    public function testThrowsIfResourcePathIsHitAsInvalidAfterBeingHitAsValid(): void
    {
        $stats = new Stats();

        $stats->hitResourcePath('foo/bar');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Identifier 'foo/bar' was already registered being valid.");

        $stats->hitResourcePath('foo/bar', false);
    }

    public function testThrowsIfResourcePathIsHitAsValidAfterBeingHitAsInvalid(): void
    {
        $stats = new Stats();

        $stats->hitResourcePath('foo/bar', false);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Identifier 'foo/bar' was already registered being invalid.");

        $stats->hitResourcePath('foo/bar');
    }
}
