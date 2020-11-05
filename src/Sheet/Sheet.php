<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Sheet;

use LiveWorksheet\Parser\Parameter\Parameter;
use Webmozart\PathUtil\Path;

final class Sheet
{
    private string $name;
    private string $content;

    /**
     * Parameter mapping: unique name => value.
     *
     * @var array<string, Parameter>
     */
    private array $parameters;

    /**
     * File path mapping: path relative to sheet root => absolute path.
     *
     * @var array<string, string>
     */
    private array $resources;

    /**
     * @internal
     */
    public function __construct(string $name, string $content, array $resources, array $parameters)
    {
        $this->name = $name;
        $this->content = $content;
        $this->resources = $resources;
        $this->parameters = $parameters;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    public function getFullName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return Path::getDirectory($this->name);
    }

    public function getName(): string
    {
        return Path::getFilename($this->name);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return array<string, string>
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @return array<string, Parameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
