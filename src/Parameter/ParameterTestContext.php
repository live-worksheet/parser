<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter;

/**
 * Context that allows test running a parameter set when linting.
 *
 * @internal
 */
final class ParameterTestContext implements ParameterContextInterface
{
    /** @var array<string, ParameterInterface> */
    private array $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameter(string $name): ParameterInterface
    {
        if (!isset($this->parameters[$name])) {
            throw new \InvalidArgumentException("Parameter '$name' was not expected to be requested.");
        }

        return $this->parameters[$name];
    }

    public function getSeed(): int
    {
        // Always return the same test seed
        return 1;
    }

    public function getUserInput(string $name)
    {
        return null;
    }
}
