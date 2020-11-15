<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\Types;

use LiveWorksheet\Parser\Parameter\Feedback\BinaryFeedback;
use LiveWorksheet\Parser\Parameter\Feedback\FeedbackInterface;
use LiveWorksheet\Parser\Parameter\ParameterContextInterface;
use LiveWorksheet\Parser\Parameter\ParameterInterface;

final class StaticType implements ParameterInterface
{
    private string $name;

    /**
     * @var array<string>
     */
    private array $allowedValues;

    private ?string $rawValue = null;

    public function __construct(string $name, string ...$allowedValues)
    {
        $this->name = $name;
        $this->allowedValues = array_values($allowedValues);

        $duplicates = array_filter(
            array_count_values($allowedValues),
            static fn (int $count): bool => $count > 1
        );

        if (!empty($duplicates)) {
            throw new \InvalidArgumentException(sprintf("Static definition '%s' contains duplicate values '%s'.", $name, implode("', '", array_keys($duplicates))));
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string>
     */
    public function getOptions(): array
    {
        return $this->allowedValues;
    }

    public function getRawValue(ParameterContextInterface $context): string
    {
        if (null === $this->rawValue) {
            $this->rawValue = $this->pickValue($context);
        }

        return $this->rawValue;
    }

    public function checkInput(ParameterContextInterface $context): FeedbackInterface
    {
        try {
            $input = (string) $context->getUserInput($this->name);
        } catch (\Throwable $e) {
            return new BinaryFeedback(false);
        }

        return new BinaryFeedback(
            \in_array($input, $this->allowedValues, true)
        );
    }

    private function pickValue(ParameterContextInterface $context): string
    {
        $numValues = \count($this->allowedValues);

        if (1 === $numValues) {
            return $this->allowedValues[0];
        }

        $key = $context->getSeed().$this->name;
        $index = crc32($key) % $numValues;

        return $this->allowedValues[$index];
    }
}
