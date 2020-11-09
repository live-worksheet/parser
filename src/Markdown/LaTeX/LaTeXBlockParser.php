<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\LaTeX;

use League\CommonMark\Block\Parser\BlockParserInterface;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;

class LaTeXBlockParser implements BlockParserInterface
{
    /**
     * Parse LaTeX blocks surrounded by at least three dollar signs ($$$).
     */
    public function parse(ContextInterface $context, Cursor $cursor): bool
    {
        if ($cursor->isIndented()) {
            return false;
        }

        if ($context->getContainer() instanceof LaTeXBlock) {
            return false;
        }

        $c = $cursor->getCharacter();

        if (' ' !== $c && "\t" !== $c && '$' !== $c) {
            return false;
        }

        $indent = $cursor->getIndent();
        $fence = $cursor->match('/^[ \t]*(\${3,}(?!.*\$))/');

        if (null === $fence) {
            return false;
        }

        $context->addBlock(new LaTeXBlock(\strlen(ltrim($fence, " \t")), $indent));

        return true;
    }
}
