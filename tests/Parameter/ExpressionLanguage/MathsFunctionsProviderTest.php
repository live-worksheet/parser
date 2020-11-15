<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\ExpressionLanguage;

use LiveWorksheet\Parser\Parameter\ExpressionLanguage\MathFunctionsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class MathsFunctionsProviderTest extends TestCase
{
    public function testProvidesFunctions(): void
    {
        $functionNames = array_map(
            static fn (ExpressionFunction $f): string => $f->getName(),
            (new MathFunctionsProvider())->getFunctions()
        );

        self::assertContains('sin', $functionNames);
        self::assertContains('cos', $functionNames);
        self::assertContains('tan', $functionNames);
        self::assertContains('asin', $functionNames);
        self::assertContains('acos', $functionNames);
        self::assertContains('atan', $functionNames);
        self::assertContains('atanh', $functionNames);
        self::assertContains('sinh', $functionNames);
        self::assertContains('cosh', $functionNames);
        self::assertContains('tanh', $functionNames);
        self::assertContains('asinh', $functionNames);
        self::assertContains('acosh', $functionNames);
        self::assertContains('deg2rad', $functionNames);
        self::assertContains('rad2deg', $functionNames);
        self::assertContains('hypot', $functionNames);
        self::assertContains('exp', $functionNames);
        self::assertContains('pow', $functionNames);
        self::assertContains('sqrt', $functionNames);
        self::assertContains('intdiv', $functionNames);
        self::assertContains('fmod', $functionNames);
        self::assertContains('abs', $functionNames);
        self::assertContains('ceil', $functionNames);
        self::assertContains('floor', $functionNames);
        self::assertContains('round', $functionNames);
    }

    /**
     * @dataProvider provideFunctions
     */
    public function testDoesNotImplementCompiler(ExpressionFunction $function): void
    {
        $compiler = $function->getCompiler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not implemented.');

        $compiler();
    }

    public function provideFunctions(): \Generator
    {
        foreach ((new MathFunctionsProvider())->getFunctions() as $function) {
            yield $function->getName() => [$function];
        }
    }
}
