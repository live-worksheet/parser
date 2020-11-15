<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * @internal
 */
final class MathFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return iterator_to_array($this->doGetFunctions(), false);
    }

    private function doGetFunctions(): \Generator
    {
        yield from $this->getNativeFunctions([
            // trigonometry
            'sin', 'cos', 'tan',
            'asin', 'acos', 'atan', 'atanh',
            'sinh', 'cosh', 'tanh',
            'asinh', 'acosh',

            // angles + helpers
            'deg2rad', 'rad2deg',
            'hypot',

            // exponential
            'exp', 'pow',
            'sqrt',

            // division
            'intdiv', 'fmod',

            // truncating
            'abs', 'ceil', 'floor', 'round',
        ]);

        yield new ExpressionFunction(
            'mod',
            $this->getNullCompiler(),
            /**
             * @param mixed $value
             * @param mixed $dividend
             */
            static fn (array $args, $value, $dividend) => (int) $value % (int) $dividend
        );

        yield new ExpressionFunction(
            'log',
            $this->getNullCompiler(),
            /**
             * @param mixed $value
             * @param mixed $base
             */
            static fn (array $args, $value, $base = 10) => log((float) $value, $base)
        );

        yield new ExpressionFunction(
            'ln',
            $this->getNullCompiler(),
            /**
             * @param mixed $value
             */
            static fn (array $args, $value) => log((float) $value)
        );
    }

    private function getNativeFunctions(array $functions): \Generator
    {
        foreach ($functions as $name) {
            $phpFunction = "\\$name";

            yield new ExpressionFunction(
                $name,
                $this->getNullCompiler(),
                /**
                 * @return float|int
                 */
                static fn () => $phpFunction(...\array_slice(\func_get_args(), 1))
            );
        }
    }

    private function getNullCompiler(): \Closure
    {
        return static function (): void {
            throw new \RuntimeException('Not implemented.');
        };
    }
}
