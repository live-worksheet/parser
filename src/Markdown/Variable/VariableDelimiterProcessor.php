<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\Variable;

use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Inline\Element\AbstractStringContainer;
use League\CommonMark\Inline\Element\Text;

class VariableDelimiterProcessor implements DelimiterProcessorInterface
{
    public function getOpeningCharacter(): string
    {
        return '{';
    }

    public function getClosingCharacter(): string
    {
        return '}';
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
        $text = $opener->next();

        if (!$text instanceof Text) {
            return;
        }

        $text->detach();

        $content = explode('?', $text->getContent(), 2);

        $opener->insertAfter(
            new Variable($content[0], isset($content[1]))
        );
    }
}
