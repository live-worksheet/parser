<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\Variable;

use League\CommonMark\Inline\Element\AbstractInline;

class Variable extends AbstractInline
{
    private string $name;

    private bool $input;

    public function __construct(string $name, bool $input = false)
    {
        $this->name = $name;
        $this->input = $input;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInput(): bool
    {
        return $this->input;
    }
}
