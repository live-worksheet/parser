<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown;

/**
 * Input context that tracks usage of its getters.
 */
class CountingInputContext extends AbstractInputContext
{
    private Stats $stats;

    public function __construct()
    {
        $this->stats = new Stats();
    }

    public function getResourcePath(string $resource): string
    {
        $valid = isset($this->resources[$resource]);
        $this->stats->hitResourcePath($resource, $valid);

        return parent::getResourcePath($resource);
    }

    public function getVariable(string $name): string
    {
        $valid = isset($this->parameters[$name]);
        $this->stats->hitVariable($name, $valid);

        return parent::getVariable($name);
    }

    public function getVariablePlaceholder(string $name): string
    {
        $valid = isset($this->parameters[$name]);
        $this->stats->hitVariablePlaceholder($name, $valid);

        return parent::getVariablePlaceholder($name);
    }

    public function getStats(): Stats
    {
        // Freeze
        return clone $this->stats;
    }
}
