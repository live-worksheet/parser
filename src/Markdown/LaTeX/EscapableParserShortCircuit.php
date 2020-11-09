<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\LaTeX;

use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;

class EscapableParserShortCircuit implements InlineParserInterface
{
    public function getCharacters(): array
    {
        return ['\\'];
    }

    /**
     * Skip escaping inside LaTeX blocks.
     */
    public function parse(InlineParserContext $inlineContext): bool
    {
        if (!$this->isLaTeXContext($inlineContext)) {
            return false;
        }

        // do not escape inside LaTeX block
        $inlineContext->getContainer()->appendChild(new Text('\\'));

        $inlineContext->getCursor()->advanceBy(1);

        return true;
    }

    private function isLaTeXContext(InlineParserContext $inlineContext): bool
    {
        $container = $inlineContext->getContainer();

        if ($container instanceof LaTeXBlock) {
            return true;
        }

        if (!$container instanceof Paragraph) {
            return false;
        }

        $delimiter = $inlineContext
            ->getDelimiterStack()
            ->searchByCharacter('$')
        ;

        return null !== $delimiter && $delimiter->getLength() >= 2;
    }
}
