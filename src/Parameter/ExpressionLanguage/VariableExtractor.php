<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\Lexer;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\ExpressionLanguage\Token;

/**
 * @internal
 */
final class VariableExtractor
{
    /**
     * @return array<string>
     */
    public static function getVariables(string $expression): array
    {
        $tokens = [];

        try {
            $tokenStream = (new Lexer())->tokenize($expression);

            while (!$tokenStream->isEOF()) {
                $tokens[] = $tokenStream->current;
                $tokenStream->next();
            }
        } catch (SyntaxError $e) {
            // We cannot identify the variables if tokenizing fails
            return [];
        }

        $variables = [];

        /* @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < \count($tokens); ++$i) {
            if (!$tokens[$i]->test(Token::NAME_TYPE)) {
                continue;
            }

            $value = $tokens[$i]->value;

            // Skip constant nodes (see Symfony/Component/ExpressionLanguage/Parser#parsePrimaryExpression()
            if (\in_array($value, ['true', 'TRUE', 'false', 'FALSE', 'null'], true)) {
                continue;
            }

            // Skip functions
            if (isset($tokens[$i + 1]) && '(' === $tokens[$i + 1]->value) {
                ++$i;

                continue;
            }

            if (!\in_array($value, $variables, true)) {
                $variables[] = $value;
            }
        }

        return $variables;
    }
}
