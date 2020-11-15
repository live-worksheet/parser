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
final class ConstantsFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return iterator_to_array($this->doGetFunctions(), false);
    }

    private function doGetFunctions(): \Generator
    {
        yield from $this->getConstantsFunctions([
            'pi' => M_PI,
            'e' => M_E,
        ]);
    }

    private function getConstantsFunctions(array $functions): \Generator
    {
        foreach ($functions as $name => $value) {
            yield new ExpressionFunction(
                $name,
                $this->getNullCompiler(),
                static fn (array $args): float => $value,
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
