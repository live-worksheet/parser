<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\ExpressionLanguage;

use LiveWorksheet\Parser\Exception\EvaluationException;
use LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom\IntegerGenerator;
use LiveWorksheet\Parser\Parameter\ExpressionLanguage\SequentialRandom\NameGenerator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

/**
 * @internal
 */
final class ExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct(int $seed, string $specimen = '')
    {
        parent::__construct();

        $integerGenerator = new IntegerGenerator($seed, $specimen);
        $nameGenerator = new NameGenerator($seed, $specimen);

        $this->registerProvider(new MathFunctionsProvider());
        $this->registerProvider(new ConstantsFunctionsProvider());
        $this->registerProvider(new RandomFunctionsProvider($integerGenerator, $nameGenerator));

        $this->disableConstantFunction();
    }

    public function evaluate($expression, array $values = [])
    {
        try {
            $return = parent::evaluate($expression, $values);
        } catch (\Throwable $e) {
            throw new EvaluationException($e->getMessage());
        }

        return $return;
    }

    private function disableConstantFunction(): void
    {
        // Disable `constant()` function.
        $this->register(
            'constant',
            static function (): void {
                throw new \RuntimeException('Not implemented.');
            },
            static function (): string {
                return '';
            }
        );
    }
}
