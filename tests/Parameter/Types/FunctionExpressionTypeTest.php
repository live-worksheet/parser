<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\Types;

use LiveWorksheet\Parser\Exception\EvaluationException;
use LiveWorksheet\Parser\Parameter\ParameterContextInterface;
use LiveWorksheet\Parser\Parameter\Types\FunctionExpressionType;
use LiveWorksheet\Parser\Parameter\Types\StaticType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class FunctionExpressionTypeTest extends TestCase
{
    public function testGetProperties(): void
    {
        $parameter = new FunctionExpressionType('Foo = 1 + 1', 'round', 5);

        self::assertEquals(FunctionExpressionType::COMPARE_MODE_ROUNDED, $parameter->getCompareMode());
        self::assertEquals(5, $parameter->getPrecision());
    }

    /**
     * @dataProvider provideExpressions
     */
    public function testParsesDefinition(string $definition, string $name, string $expression, array $dependsOn): void
    {
        $parameter = new FunctionExpressionType($definition, 'exact', 0);

        self::assertEquals($name, $parameter->getName());
        self::assertEquals($expression, $parameter->getExpression());
        self::assertEquals($dependsOn, $parameter->dependsOn());
    }

    public function provideExpressions(): \Generator
    {
        yield 'simple' => [
            'A = 1 + 1',
            'A',
            '1 + 1',
            [],
        ];

        yield 'with extra spaces' => [
            '   B   =  1 + 1  ',
            'B',
            '1 + 1',
            [],
        ];

        yield 'depends on functions' => [
            ' Foo = A + (2 * B) + C',
            'Foo',
            'A + (2 * B) + C',
            ['A', 'B', 'C'],
        ];
    }

    /**
     * @dataProvider provideInvalidExpressions
     */
    public function testThrowsIfExpressionIsInvalid(string $expression): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/^Function definition '.+' must contain exactly one '=' sign\\.$/");

        new FunctionExpressionType($expression, 'exact', 0);
    }

    public function provideInvalidExpressions(): \Generator
    {
        yield 'missing equal sign' => [
            'A B + 5',
        ];

        yield 'multiple equal signs' => [
            'Foo = Bar = FooBar',
        ];
    }

    public function testGetRawValue(): void
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

        $parameter = new FunctionExpressionType('Foo = 5', 'exact', 0);

        self::assertEquals(5, $parameter->getRawValue($context));
    }

    public function testGetRawValueWithDependentFunctions(): void
    {
        /** @var ParameterContextInterface&MockObject $context */
        $context = $this->createMock(ParameterContextInterface::class);

        $context
            ->method('getUserInput')
            ->willReturn(null)
        ;

        $context
            ->method('getSeed')
            ->willReturn(1)
        ;

        $context
            ->method('getParameter')
            ->with('Bar')
            ->willReturn(new StaticType('Bar', '3'))
        ;

        $parameter = new FunctionExpressionType('Foo = 5 + Bar', 'exact', 0);

        self::assertEquals(8, $parameter->getRawValue($context));
    }

    public function testGetRawValueWithFailingFunction(): void
    {
        /** @var ParameterContextInterface&MockObject $context */
        $context = $this->createMock(ParameterContextInterface::class);

        $context
            ->method('getUserInput')
            ->willReturn(null)
        ;

        $context
            ->method('getSeed')
            ->willReturn(1)
        ;

        $parameter = new FunctionExpressionType('Foo = Invalid()', 'exact', 0);

        $this->expectException(EvaluationException::class);
        $this->expectExceptionMessageMatches("/Syntax error in function 'Foo': .*\\./");

        $parameter->getRawValue($context);
    }

    /**
     * @dataProvider provideUserInputs
     *
     * @param string|float|int $value
     */
    public function testCheckInput($value, string $compareMode, int $precision, bool $expectedResult): void
    {
        /** @var ParameterContextInterface&MockObject $context */
        $context = $this->createMock(ParameterContextInterface::class);

        $context
            ->method('getUserInput')
            ->with('Foo')
            ->willReturn($value)
        ;

        $context
            ->method('getSeed')
            ->willReturn(1)
        ;

        $parameter = new FunctionExpressionType('Foo = 3.14159', $compareMode, $precision);

        $result = $parameter->checkInput($context)->isCorrect();

        self::assertEquals($expectedResult, $result);
    }

    public function provideUserInputs(): \Generator
    {
        yield 'correct exact value' => [
            '3.14159', 'exact', 0, true,
        ];

        yield 'correct rounded value (4 digits)' => [
            '3.1416', 'round', 4, true,
        ];

        yield 'correct rounded value (default)' => [
            '3', 'round', 0, true,
        ];

        yield 'incorrect exact value' => [
            '3.14', 'exact', 0, false,
        ];

        yield 'incorrect rounded value (too precise)' => [
            '3.14159265', 'round', 8, false,
        ];

        yield 'incorrect rounded value (not precise enough)' => [
            '3.142', 'round', 4, false,
        ];

        yield 'comma as separator' => [
            '3,14159', 'exact', 0, true,
        ];

        yield 'with spaces' => [
            ' 3.14159 ', 'exact', 0, true,
        ];

        yield 'with sign' => [
            '+ 3.14159 ', 'exact', 0, true,
        ];

        yield 'integer input' => [
            3, 'round', 0, true,
        ];

        yield 'float input' => [
            3.14, 'round', 2, true,
        ];
    }

    public function testCheckInputWithInvalidType(): void
    {
        /** @var ParameterContextInterface&MockObject $context */
        $context = $this->createMock(ParameterContextInterface::class);

        $context
            ->method('getUserInput')
            ->with('Foo')
            ->willReturn(new stdClass())
        ;

        $context
            ->method('getSeed')
            ->willReturn(1)
        ;

        $parameter = new FunctionExpressionType('Foo = 2', 'exact', 0);

        $result = $parameter->checkInput($context)->isCorrect();

        self::assertFalse($result);
    }

    public function testCheckInputWithInvalidCompareMode(): void
    {
        $parameter = new FunctionExpressionType('Foo = 1 + 1', 'invalid', 5);

        /** @var ParameterContextInterface&MockObject $context */
        $context = $this->createMock(ParameterContextInterface::class);

        $context
            ->method('getUserInput')
            ->with('Foo')
            ->willReturn(2)
        ;

        $context
            ->method('getSeed')
            ->willReturn(1)
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Unknown compare mode 'invalid'.");

        $parameter->checkInput($context);
    }
}
