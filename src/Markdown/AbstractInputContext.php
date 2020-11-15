<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown;

use LiveWorksheet\Parser\Parameter\ParameterInterface;

abstract class AbstractInputContext implements InputContextInterface
{
    protected string $content = '';

    /** @var array<string, string> */
    protected array $resources = [];

    /** @var array<string, ParameterInterface> */
    protected array $parameters = [];

    public function getContent(): string
    {
        return $this->content;
    }

    public function getResourcePath(string $resource): string
    {
        return $this->resources[$resource] ?? '';
    }

    public function getVariable(string $name): string
    {
        return isset($this->parameters[$name]) ? $name : '';
    }

    public function getVariablePlaceholder(string $name): string
    {
        return isset($this->parameters[$name]) ? $name : '';
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @param array<string, string> $resources
     */
    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    /**
     * @param array<ParameterInterface> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $mappedParameters = [];

        foreach ($parameters as $parameter) {
            $mappedParameters[$parameter->getName()] = $parameter;
        }

        $this->parameters = $mappedParameters;
    }
}
