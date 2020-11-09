<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown;

/**
 * Register input usage.
 */
class Stats
{
    private const TYPE_RESOURCE_PATHS = 'resource_paths';
    private const TYPE_VARIABLES = 'variables';
    private const TYPE_VARIABLE_PLACEHOLDERS = 'variable_placeholders';

    /** @var array<string, array<string, int>> */
    private array $hits = [];

    public function hitResourcePath(string $path, bool $valid = true): void
    {
        $this->hit(self::TYPE_RESOURCE_PATHS, $path, $valid);
    }

    public function hitVariable(string $name, bool $valid = true): void
    {
        $this->hit(self::TYPE_VARIABLES, $name, $valid);
    }

    public function hitVariablePlaceholder(string $name, bool $valid = true): void
    {
        $this->hit(self::TYPE_VARIABLE_PLACEHOLDERS, $name, $valid);
    }

    /**
     * @return array<string, int>
     */
    public function getResourcesCounts(bool $valid = true): array
    {
        return $this->get(self::TYPE_RESOURCE_PATHS, $valid);
    }

    /**
     * @return array<string, int>
     */
    public function getVariablesCounts(bool $valid = true): array
    {
        return $this->get(self::TYPE_VARIABLES, $valid);
    }

    /**
     * @return array<string, int>
     */
    public function getVariablePlaceholderCounts(bool $valid = true): array
    {
        return $this->get(self::TYPE_VARIABLE_PLACEHOLDERS, $valid);
    }

    private function hit(string $category, string $identifier, bool $valid = null): void
    {
        if (null !== $valid) {
            $otherCategory = $this->getCategory($category, !$valid);

            if (isset($this->hits[$otherCategory][$identifier])) {
                throw new \LogicException(sprintf("Identifier '%s' was already registered being %s.", $identifier, $valid ? 'invalid' : 'valid'));
            }
        }

        $effectiveCategory = $this->getCategory($category, $valid);

        // Initialize or increase count
        if (!isset($this->hits[$effectiveCategory][$identifier])) {
            $this->hits[$effectiveCategory][$identifier] = 1;
        } else {
            ++$this->hits[$effectiveCategory][$identifier];
        }
    }

    /**
     * @return array<string, int>
     */
    private function get(string $category, bool $valid = null): array
    {
        $effectiveCategory = $this->getCategory($category, $valid);

        return $this->hits[$effectiveCategory] ?? [];
    }

    private function getCategory(string $identifier, ?bool $valid): string
    {
        if (false === $valid) {
            $identifier = '?'.$identifier;
        }

        return $identifier;
    }
}
