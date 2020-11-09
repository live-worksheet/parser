<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\LaTeX;

use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Inline\Element\AbstractStringContainer;

class LaTeXDelimiterProcessor implements DelimiterProcessorInterface
{
    public function getOpeningCharacter(): string
    {
        return '$';
    }

    public function getClosingCharacter(): string
    {
        return '$';
    }

    public function getMinLength(): int
    {
        return 2;
    }

    public function getDelimiterUse(DelimiterInterface $opener, DelimiterInterface $closer): int
    {
        $min = min($opener->getLength(), $closer->getLength());

        return $min >= 2 ? $min : 0;
    }

    public function process(AbstractStringContainer $opener, AbstractStringContainer $closer, int $delimiterUse): void
    {
        $latex = new LaTeX();

        $tmp = $opener->next();

        while (null !== $tmp && $tmp !== $closer) {
            $next = $tmp->next();
            $latex->appendChild($tmp);
            $tmp = $next;
        }

        $opener->insertAfter($latex);
    }
}
