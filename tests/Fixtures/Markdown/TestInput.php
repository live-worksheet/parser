<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Fixtures\Markdown;

use LiveWorksheet\Parser\Markdown\AbstractInputContext;

class TestInput extends AbstractInputContext
{
    public function getVariablePlaceholder(string $name): string
    {
        $placeholder = parent::getVariablePlaceholder($name);

        // Make variable placeholders distinguishable in the output
        return '' !== $placeholder ? sprintf('?%s', $placeholder) : '';
    }
}
