<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\LaTeX;

use League\CommonMark\Inline\Element\AbstractInline;

class LaTeX extends AbstractInline
{
    public function isContainer(): bool
    {
        return true;
    }
}
