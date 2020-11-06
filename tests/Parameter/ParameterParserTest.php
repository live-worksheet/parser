<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter;

use LiveWorksheet\Parser\Exception\ParserException;
use LiveWorksheet\Parser\Parameter\Parameter;
use LiveWorksheet\Parser\Parameter\ParameterParser;
use PHPUnit\Framework\TestCase;

class ParameterParserTest extends TestCase
{
    /**
     * @dataProvider provideDefinitions
     */
    public function testParse(string $definition, array $expected): void
    {
        $parser = new ParameterParser();

        $parameter = $parser->parse($definition);

        [$name, $expression, $mode, $precision] = $expected;

        self::assertNotNull($parameter);
        self::assertEquals($name, $parameter->getName());
        self::assertEquals($expression, $parameter->getExpression());
        self::assertEquals($mode, $parameter->getMode());
        self::assertEquals($precision, $parameter->getPrecision());
    }

    public function provideDefinitions(): \Generator
    {
        yield 'regular' => [
            'Foo=Bar',
            [
                'Foo',
                'Bar',
                Parameter::MODE__EXACT,
                0,
            ],
        ];

        yield 'with additional spaces' => [
            '  Foo  =  Bar  ',
            [
                'Foo',
                'Bar',
                Parameter::MODE__EXACT,
                0,
            ],
        ];

        yield 'with parameter mode' => [
            'Foo = Bar | fraction',
            [
                'Foo',
                'Bar',
                Parameter::MODE__FRACTION,
                0,
            ],
        ];

        yield 'full' => [
            'FooBar = bar(x) + 2 * foo | round 2',
            [
                'FooBar',
                'bar(x) + 2 * foo',
                Parameter::MODE__ROUNDED,
                2,
            ],
        ];
    }

    /**
     * @dataProvider provideInvalidDefinitions
     */
    public function testParseReturnsNullIfDefinitionIsInvalid(string $definition): void
    {
        $parser = new ParameterParser();

        $parameter = $parser->parse($definition);

        self::assertNull($parameter);
    }

    /**
     * @dataProvider provideInvalidDefinitions
     */
    public function testParseThrowsIfIfDefinitionIsInvalid(string $definition, string $exception): void
    {
        $parser = new ParameterParser();

        $this->expectException(ParserException::class);
        $this->expectExceptionMessageMatches($exception);

        $parser->parse($definition, true);
    }

    public function provideInvalidDefinitions(): \Generator
    {
        yield 'invalid mode' => [
            'Foo = Bar | foobar',
            "/Invalid constraint: Unknown mode 'foobar'\\./",
        ];

        yield 'missing equal sign' => [
            'Foo Bar | round',
            "/Could not parse definition: '.*'\\./",
        ];

        yield 'bad variable name' => [
            '2Foo = Bar',
            "/Could not parse definition: '.*'\\./",
        ];

        yield 'more than one pipe' => [
            'Foo = Bar | round |',
            "/Could not parse definition: '.*'\\./",
        ];

        yield 'non-numeric precision' => [
            'Foo = Bar | foobar x',
            "/Could not parse definition: '.*'\\./",
        ];
    }

    public function testParseAllIgnoresEmptyLinesAndComments(): void
    {
        $parser = new ParameterParser();

        $parameters = $parser->parseAll(
            " Foo = 1 + 1 | fraction\n".
            "\n".
            "# some\n".
            " ## comments ##\n".
            "Bar =  FooBar()   | round 5\n"
        );

        self::assertCount(2, $parameters);
        self::assertEquals('Foo = 1 + 1 | fraction 0', (string) $parameters['Foo']);
        self::assertEquals('Bar = FooBar() | round 5', (string) $parameters['Bar']);
    }
}
