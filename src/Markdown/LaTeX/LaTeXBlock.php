<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\LaTeX;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\AbstractStringContainerBlock;
use League\CommonMark\Block\Element\InlineContainerInterface;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use League\CommonMark\Util\RegexHelper;

class LaTeXBlock extends AbstractStringContainerBlock implements InlineContainerInterface
{
    private int $length;
    private int $offset;

    /** @var array<string> */
    private array $lines = [];

    public function __construct(int $length, int $offset)
    {
        parent::__construct();

        $this->length = $length;
        $this->offset = $offset;
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function canContain(AbstractBlock $block): bool
    {
        return false;
    }

    public function isCode(): bool
    {
        return false;
    }

    public function shouldLastLineBeBlank(Cursor $cursor, int $currentLineNumber): bool
    {
        return false;
    }

    public function matchesNextLine(Cursor $cursor): bool
    {
        if (-1 === $this->length) {
            if ($cursor->isBlank()) {
                $this->lastLineBlank = true;
            }

            return false;
        }

        // Skip optional spaces of fence offset
        $cursor->match('/^ {0,'.$this->offset.'}/');

        return true;
    }

    public function handleRemainingContents(ContextInterface $context, Cursor $cursor): void
    {
        /** @var self $container */
        $container = $context->getContainer();

        // check for closing LaTeX fence
        if ($cursor->getIndent() <= 3 && '$' === $cursor->getNextNonSpaceCharacter()) {
            $match = RegexHelper::matchAll(
                '/^(?:\${3,})(?= *$)/',
                $cursor->getLine(),
                $cursor->getNextNonSpacePosition()
            );

            if (null !== $match && \strlen($match[0]) >= $container->getLength()) {
                // Don't add closing fence to container; instead, close it
                $this->setLength(-1); // -1 means we've passed closer

                return;
            }
        }

        $container->addLine($cursor->getRemainder());
    }

    public function addLine(string $line): void
    {
        $this->lines[] = $line;
    }

    public function finalize(ContextInterface $context, int $endLineNumber): void
    {
        parent::finalize($context, $endLineNumber);

        $this->finalStringContents = implode("\n", $this->lines);
    }
}
