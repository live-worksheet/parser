<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Sheet;

use Webmozart\PathUtil\Path;

final class Sheet
{
    private string $name;
    private string $content;
    private string $parameters;

    /**
     * File path mapping: path relative to sheet root => absolute path.
     *
     * @var array<string, string>
     */
    private array $resources;

    /**
     * @internal
     */
    public function __construct(string $name, string $content, string $parameters = '', array $resources = [])
    {
        $this->name = $name;
        $this->content = $content;
        $this->parameters = $parameters;
        $this->resources = $resources;
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

    public function getParameters(): string
    {
        return $this->parameters;
    }

    /**
     * @return array<string, string>
     */
    public function getResources(): array
    {
        return $this->resources;
    }
}
