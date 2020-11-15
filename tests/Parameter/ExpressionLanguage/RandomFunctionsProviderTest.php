<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\ExpressionLanguage;

use LiveWorksheet\Parser\Parameter\ExpressionLanguage\RandomFunctionsProvider;
use LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom\IntegerGenerator;
use LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom\NameGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class RandomFunctionsProviderTest extends TestCase
{
    public function testProvidesFunctions(): void
    {
        $functionNames = array_keys($this->getFunctions());

        self::assertContains('integer', $functionNames);
        self::assertContains('name', $functionNames);
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
        foreach ($this->getFunctions() as $name => $function) {
            yield $name => [$function];
        }
    }

    public function testDelegatesCalls(): void
    {
        /** @var IntegerGenerator&MockObject $integerGenerator */
        $integerGenerator = $this->createMock(IntegerGenerator::class);
        $integerGenerator
            ->expects(self::once())
            ->method('next')
            ->with(5, 15)
            ;

        /** @var NameGenerator&MockObject $nameGenerator */
        $nameGenerator = $this->createMock(NameGenerator::class);
        $nameGenerator
            ->expects(self::once())
            ->method('next')
            ->with('f,m')
        ;

        $functions = $this->getFunctions($integerGenerator, $nameGenerator);

        ($functions['integer']->getEvaluator())([], 5, 15);
        ($functions['name']->getEvaluator())([], 'f,m');
    }

    /**
     * @param IntegerGenerator&MockObject $integerGenerator
     * @param NameGenerator&MockObject    $nameGenerator
     *
     * @return array<string, ExpressionFunction>
     */
    private function getFunctions($integerGenerator = null, $nameGenerator = null): array
    {
        $functions = (new RandomFunctionsProvider(
            $integerGenerator ?? $this->createMock(IntegerGenerator::class),
            $nameGenerator ?? $this->createMock(NameGenerator::class),
        ))->getFunctions();

        $functionsByName = [];

        foreach ($functions as $function) {
            $functionsByName[$function->getName()] = $function;
        }

        return $functionsByName;
    }
}
