<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\ExpressionLanguage;

use LiveWorksheet\Parser\Exception\EvaluationException;
use LiveWorksheet\Parser\Parameter\ExpressionLanguage\ExpressionLanguage;
use PHPUnit\Framework\TestCase;

class ExpressionLanguageTest extends TestCase
{
    public function testDisablesConstantFunction(): void
    {
        $expressionLanguage = new ExpressionLanguage(1);

        self::assertEquals('', $expressionLanguage->evaluate('constant()'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not implemented');

        $expressionLanguage->compile('constant()');
    }

    public function testEvaluatesFunctions(): void
    {
        $expressionLanguage = new ExpressionLanguage(2);

        $expressionLanguage->register(
            'foobar',
            static fn () => null,
            static function (): void {
                throw new \RuntimeException('Something is not working.');
            }
        );

        self::assertEquals('19', $expressionLanguage->evaluate('4 + (3 * A)', ['A' => 5]));
        self::assertEquals('1', $expressionLanguage->evaluate('cos(2 * pi())'));

        $this->expectException(EvaluationException::class);
        $this->expectExceptionMessage('Something is not working');

        $expressionLanguage->evaluate('foobar()');
    }
}
