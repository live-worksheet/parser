<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\ExpressionLanguage;

use LiveWorksheet\Parser\Parameter\ExpressionLanguage\ConstantsFunctionsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class ConstantsFunctionsProviderTest extends TestCase
{
    public function testProvidesFunctions(): void
    {
        $functionNames = array_map(
            static fn (ExpressionFunction $f): string => $f->getName(),
            (new ConstantsFunctionsProvider())->getFunctions()
        );

        self::assertContains('pi', $functionNames);
        self::assertContains('e', $functionNames);
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
        foreach ((new ConstantsFunctionsProvider())->getFunctions() as $function) {
            yield $function->getName() => [$function];
        }
    }
}
