<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\Types;

use LiveWorksheet\Parser\Exception\EvaluationException;
use LiveWorksheet\Parser\Parameter\DependentParameterInterface;
use LiveWorksheet\Parser\Parameter\ExpressionLanguage\ExpressionLanguage;
use LiveWorksheet\Parser\Parameter\ExpressionLanguage\VariableExtractor;
use LiveWorksheet\Parser\Parameter\Feedback\BinaryFeedback;
use LiveWorksheet\Parser\Parameter\Feedback\FeedbackInterface;
use LiveWorksheet\Parser\Parameter\ParameterContextInterface;
use LiveWorksheet\Parser\Parameter\ParameterInterface;
use Symfony\Component\ExpressionLanguage\SyntaxError;

final class FunctionExpressionType implements ParameterInterface, DependentParameterInterface
{
    public const COMPARE_MODE_EXACT = 'exact';
    public const COMPARE_MODE_ROUNDED = 'round';

    public const COMPARE_MODES = [
        self::COMPARE_MODE_EXACT,
        self::COMPARE_MODE_ROUNDED,
    ];

    private string $name;
    private string $valueExpression;

    /**
     * @var array<string> a list of parameter names this parameter depends on
     */
    private array $dependentParameters;

    private string $compareMode;
    private int $precision;

    /**
     * @var mixed the result of the expression once evaluated, null by default
     */
    private $rawValue;

    public function __construct(string $definition, string $compareMode = 'exact', int $precision = 0)
    {
        $parts = explode('=', $definition);

        if (2 !== \count($parts)) {
            throw new \InvalidArgumentException("Function definition '$definition' must contain exactly one '=' sign.");
        }

        $this->name = trim($parts[0]);
        $this->valueExpression = trim($parts[1]);

        $this->dependentParameters = VariableExtractor::getVariables($this->valueExpression);

        $this->compareMode = $compareMode;
        $this->precision = $precision;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCompareMode(): string
    {
        return $this->compareMode;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getExpression(): string
    {
        return $this->valueExpression;
    }

    public function dependsOn(): array
    {
        return $this->dependentParameters;
    }

    /**
     * @return mixed
     */
    public function getRawValue(ParameterContextInterface $context)
    {
        if (null === $this->rawValue) {
            $this->rawValue = $this->evaluate($context) ?? '?';
        }

        return $this->rawValue;
    }

    public function checkInput(ParameterContextInterface $context): FeedbackInterface
    {
        try {
            $right = $this->normalize((string) $context->getUserInput($this->name));
            $left = (float) $this->getRawValue($context);
        } catch (\Throwable $e) {
            return new BinaryFeedback(false);
        }

        if (self::COMPARE_MODE_EXACT === $this->compareMode) {
            return new BinaryFeedback(
                (string) $left === $right
            );
        }

        if (self::COMPARE_MODE_ROUNDED === $this->compareMode) {
            return new BinaryFeedback(
                (string) round($left, $this->precision) === $right
            );
        }

        throw new \RuntimeException("Unknown compare mode '{$this->compareMode}'.");
    }

    /**
     * @return mixed
     */
    private function evaluate(ParameterContextInterface $context)
    {
        $parameters = [];

        foreach ($this->dependentParameters as $dependentParameter) {
            $parameters[$dependentParameter] = $context
                ->getParameter($dependentParameter)
                ->getRawValue($context)
            ;
        }

        $expressionLanguage = new ExpressionLanguage($context->getSeed(), $this->name);

        try {
            return $expressionLanguage->evaluate($this->valueExpression, $parameters);
        } catch (SyntaxError | EvaluationException $e) {
            throw new EvaluationException(sprintf("Syntax error in function '%s': %s.", $this->name, $e->getMessage()), 0, $e);
        }
    }

    private function normalize(string $number): string
    {
        $number = str_replace([',', '+', ' '], ['.', '', ''], $number);
        $parts = explode('.', $number);

        $base = (int) $parts[0];
        $output = (string) $base;

        if (isset($parts[1])) {
            $decimal = rtrim($parts[1], '0');

            if ('' !== $decimal) {
                $output .= '.'.$decimal;
            }
        }

        return $output;
    }
}
