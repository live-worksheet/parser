<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\Types;

use LiveWorksheet\Parser\Parameter\ParameterContextInterface;
use LiveWorksheet\Parser\Parameter\Types\StaticType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class StaticTypeTest extends TestCase
{
    public function testGetNameAndOptions(): void
    {
        $parameter = new StaticType('Foo', 'Bar', 'FooBar');

        self::assertEquals('Foo', $parameter->getName());
        self::assertEquals(['Bar', 'FooBar'], $parameter->getOptions());
    }

    /**
     * @dataProvider provideValues
     */
    public function testGetRawValue(array $values, string $selectedValue): void
    {
        /** @var ParameterContextInterface&MockObject $context */
        $context = $this->createMock(ParameterContextInterface::class);

        $context
            ->method('getUserInput')
            ->with('Foo')
            ->willReturn(null)
        ;

        $context
            ->method('getSeed')
            ->willReturn(1)
        ;

        $parameter = new StaticType('Foo', ...$values);

        self::assertEquals($selectedValue, $parameter->getRawValue($context));
    }

    public function provideValues(): \Generator
    {
        yield 'three values sample' => [
            ['A', 'B', 'C'], 'B',
        ];

        yield 'single value sample' => [
            ['A'], 'A',
        ];
    }

    public function testPicksValueFair(): void
    {
        $numValues = 5;
        $numIterations = 2000;
        $allowedError = 0.01;

        $buckets = array_combine(
            array_map(
                static fn (int $v): string => "Value$v",
                range(0, $numValues - 1)
            ),
            array_fill(0, $numValues, 0)
        );

        for ($i = 0; $i < $numIterations; ++$i) {
            /** @var ParameterContextInterface&MockObject $context */
            $context = $this->createMock(ParameterContextInterface::class);

            $context
                ->method('getUserInput')
                ->with('Foo')
                ->willReturn(null)
            ;

            $context
                ->method('getSeed')
                ->willReturn($i)
            ;

            $parameter = new StaticType('Foo', ...array_keys($buckets));

            ++$buckets[$parameter->getRawValue($context)];
        }

        foreach ($buckets as $count) {
            $error = abs($count - ($numIterations / $numValues)) / $numIterations;
            self::assertLessThan($allowedError, $error);
        }
    }

    /**
     * @dataProvider provideUserInputs
     *
     * @param mixed $input
     */
    public function testCheckInput($input, bool $expectedResult): void
    {
        /** @var ParameterContextInterface&MockObject $context */
        $context = $this->createMock(ParameterContextInterface::class);

        $context
            ->method('getUserInput')
            ->with('Foo')
            ->willReturn($input)
        ;

        $parameter = new StaticType('Foo', 'Bar1', 'Bar2', 'Bar3');

        $result = $parameter->checkInput($context)->isCorrect();

        self::assertEquals($expectedResult, $result);
    }

    public function provideUserInputs(): \Generator
    {
        yield 'any correct value' => [
            'Bar3', true,
        ];

        yield 'invalid value' => [
            'Bar5', false,
        ];

        yield 'invalid type' => [
            new stdClass(), false,
        ];
    }
}
