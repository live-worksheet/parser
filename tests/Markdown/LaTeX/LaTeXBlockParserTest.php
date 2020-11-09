<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\LaTeX;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXBlock;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXBlockParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LaTeXBlockParserTest extends TestCase
{
    /**
     * @dataProvider provideParseContexts
     */
    public function testParse(Cursor $cursor, ?array $fenceAndIndent): void
    {
        $expectedOutput = false;

        /** @var AbstractBlock&MockObject $container */
        $container = $this->createMock(AbstractBlock::class);

        /** @var ContextInterface&MockObject $context */
        $context = $this->createMock(ContextInterface::class);
        $context
            ->method('getContainer')
            ->willReturn($container)
        ;

        if (null !== $fenceAndIndent) {
            [$length, $offset] = $fenceAndIndent;

            $blockMatches = static fn (LaTeXBlock $block): bool => $length === $block->getLength() &&
                $offset === $block->getOffset();

            $context
                ->expects(self::once())
                ->method('addBlock')
                ->with(self::callback($blockMatches))
            ;

            $expectedOutput = true;
        }

        $parser = new LaTeXBlockParser();

        $output = $parser->parse($context, $cursor);

        self::assertEquals($expectedOutput, $output);
    }

    public function provideParseContexts(): \Generator
    {
        yield 'skips if start sequence too short' => [
            new Cursor('$$ foo'),
            null,
        ];

        yield '3 char start sequence' => [
            new Cursor('$$$ foo'),
            [3, 0],
        ];

        yield 'longer start sequence' => [
            new Cursor('$$$$$$ foo'),
            [6, 0],
        ];

        yield 'with indent (2 spaces)' => [
            new Cursor('  $$$$ foo'),
            [4, 2],
        ];

        yield 'skips large indent (>=4 spaces)' => [
            new Cursor('    $$$$ foo'),
            null,
        ];

        yield 'skips large indent (tabs)' => [
            new Cursor("\t\t$$$ foo"),
            null,
        ];

        yield 'skips if not matching at all' => [
            new Cursor('foo'),
            null,
        ];
    }

    public function testSkipsParseIfAlreadyInLaTeXContext(): void
    {
        /** @var ContextInterface&MockObject $context */
        $context = $this->createMock(ContextInterface::class);
        $context
            ->method('getContainer')
            ->willReturn(new LaTeXBlock(3, 0))
        ;

        $parser = new LaTeXBlockParser();

        $output = $parser->parse($context, new Cursor('$$$ foo'));

        self::assertFalse($output);
    }
}
