<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown;

interface InputContextInterface
{
    public function getContent(): string;

    public function getResourcePath(string $resource): string;

    public function getVariable(string $name): string;

    public function getVariablePlaceholder(string $name): string;
}
