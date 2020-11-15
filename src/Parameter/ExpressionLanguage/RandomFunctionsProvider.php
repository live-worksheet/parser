<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\ExpressionLanguage;

use LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom\IntegerGenerator;
use LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom\NameGenerator;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * @internal
 */
final class RandomFunctionsProvider implements ExpressionFunctionProviderInterface
{
    private IntegerGenerator $integerGenerator;
    private NameGenerator $nameGenerator;

    public function __construct(IntegerGenerator $integerGenerator, NameGenerator $nameGenerator)
    {
        $this->integerGenerator = $integerGenerator;
        $this->nameGenerator = $nameGenerator;
    }

    public function getFunctions(): array
    {
        return iterator_to_array($this->doGetFunctions(), false);
    }

    private function doGetFunctions(): \Generator
    {
        yield new ExpressionFunction(
            'integer',
            $this->getNullCompiler(),
            /**
             * @param mixed $min
             * @param mixed $max
             */
            function (array $args, $min = 0, $max = 1000) {
                return $this->integerGenerator->next((int) $min, (int) $max);
            },
        );

        yield new ExpressionFunction(
            'name',
            $this->getNullCompiler(),
            /**
             * @param mixed $flags
             */
            function (array $args, $flags = 'f,m') {
                return $this->nameGenerator->next((string) $flags);
            },
        );
    }

    private function getNullCompiler(): \Closure
    {
        return static function (): void {
            throw new \RuntimeException('Not implemented.');
        };
    }
}
