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
use PHPUnit\Framework\TestCase;

class LaTeXBlockTest extends TestCase
{
    public function testNonDynamicProperties(): void
    {
        $latexBlock = new LaTeXBlock(5, 2);

        self::assertFalse($latexBlock->canContain(
            $this->createMock(AbstractBlock::class)
        ));

        self::assertFalse($latexBlock->isCode());

        self::assertFalse($latexBlock->shouldLastLineBeBlank(
            $this->createMock(Cursor::class), 1
        ));
    }

    public function testGetAndSetProperties(): void
    {
        $latexBlock = new LaTeXBlock(5, 2);

        self::assertEquals(5, $latexBlock->getLength());
        self::assertEquals(2, $latexBlock->getOffset());

        $latexBlock->setLength(4);

        self::assertEquals(4, $latexBlock->getLength());
    }

    /**
     * @dataProvider provideLineMatchSamples
     */
    public function testMatchesNextLine(int $length, int $offset, ?string $cursorMatch, bool $shouldMatch): void
    {
        $cursor = $this->createMock(Cursor::class);
        $cursor
            ->method('isBlank')
            ->willReturn(true)
        ;

        if (null !== $cursorMatch) {
            $cursor
                ->expects(self::once())
                ->method('match')
                ->with($cursorMatch)
            ;
        }

        $latexBlock = new LaTeXBlock($length, $offset);

        self::assertEquals($shouldMatch, $latexBlock->matchesNextLine($cursor));
    }

    public function provideLineMatchSamples(): \Generator
    {
        // length, offset, cursorMatch, shouldMatch

        yield 'reached end' => [
            -1, 0, null, false,
        ];

        yield 'reached end (with offset)' => [
            -1, 5, null, false,
        ];

        yield 'not skipping' => [
            1, 0, '/^ {0,0}/', true,
        ];

        yield 'skipping offset' => [
            1, 5, '/^ {0,5}/', true,
        ];
    }

    /**
     * @dataProvider provideRemainingContentsSamples
     */
    public function testHandlesRemainingContents(string $line, ?string $remainder, ?int $containerLength, int $blockLength): void
    {
        $container = $this->createMock(LaTeXBlock::class);

        if (null !== $containerLength) {
            $container
                ->method('getLength')
                ->willReturn($containerLength)
            ;
        } else {
            $container
                ->expects(self::never())
                ->method('getLength')
            ;
        }

        $container
            ->expects(null !== $remainder ? self::once() : self::never())
            ->method('addLine')
            ->with($remainder)
        ;

        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects(self::once())
            ->method('getContainer')
            ->willReturn($container)
        ;

        $cursor = new Cursor($line);

        $latexBlock = new LaTeXBlock(5, 0);

        $latexBlock->handleRemainingContents($context, $cursor);

        self::assertEquals($blockLength, $latexBlock->getLength());
    }

    public function provideRemainingContentsSamples(): \Generator
    {
        // line, remainder, containerLength, blockLength

        yield 'not matching' => [
            'foo', 'foo', null, 5,
        ];

        yield 'not matching (too short)' => [
            '$$$', '$$$', 4, 5,
        ];

        yield 'matching' => [
            '$$$', null, 3, -1,
        ];

        yield 'matching with spaces' => [
            '  $$$', null, 3, -1,
        ];

        yield 'matching with spaces and additional chars' => [
            '  $$$$$', null, 3, -1,
        ];
    }

    public function testFinalize(): void
    {
        $latexBlock = new LaTeXBlock(3, 0);

        $latexBlock->addLine('foo');
        $latexBlock->addLine('bar');

        self::assertEquals('', $latexBlock->getStringContent());

        $latexBlock->finalize(
            $this->createMock(ContextInterface::class),
            5
        );

        self::assertEquals("foo\nbar", $latexBlock->getStringContent());
    }
}
