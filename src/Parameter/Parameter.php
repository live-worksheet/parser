<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter;

final class Parameter
{
    public const MODE__FRACTION = 'fraction';
    public const MODE__MIXED_FRACTION = 'mixed_fraction';
    public const MODE__ANY_FRACTION = 'any_fraction';
    public const MODE__ROUNDED = 'round';
    public const MODE__ROUNDED_SIGNIFICANT = 'round_sign';
    public const MODE__EXACT = 'exact';

    private string $name;
    private string $expression;
    private string $mode;
    private int $precision;

    /**
     * @internal
     */
    public function __construct(string $name, string $expression, string $mode = null, int $precision = null)
    {
        $this->name = $name;
        $this->expression = $expression;
        $this->mode = $mode ?? self::MODE__EXACT;
        $this->precision = $precision ?? 0;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }
}
