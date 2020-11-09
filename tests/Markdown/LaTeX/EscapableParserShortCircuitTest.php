<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\LaTeX;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\HtmlBlock;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Cursor;
use League\CommonMark\Delimiter\Delimiter;
use League\CommonMark\Delimiter\DelimiterStack;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\InlineParserContext;
use LiveWorksheet\Parser\Markdown\LaTeX\EscapableParserShortCircuit;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXBlock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EscapableParserShortCircuitTest extends TestCase
{
    public function testMatchesEscapeCharacter(): void
    {
        $parser = new EscapableParserShortCircuit();

        self::assertEquals(['\\'], $parser->getCharacters());
    }

    public function testSkipEscapingInLaTeXBlock(): void
    {
        $context = $this->getContext(new LaTeXBlock(3, 0));

        $parser = new EscapableParserShortCircuit();

        self::assertTrue($parser->parse($context));

        $node = $context->getContainer()->firstChild();

        self::assertInstanceOf(Text::class, $node);
        self::assertEquals('\\', $node->getContent());
    }

    public function testSkipEscapingInlineLaTeX(): void
    {
        $context = $this->getContext(new Paragraph());

        $stack = new DelimiterStack();
        $stack->push(
            new Delimiter('$', 2, new Text('foo'), true, true)
        );

        $context
            ->method('getDelimiterStack')
            ->willReturn($stack)
        ;

        $parser = new EscapableParserShortCircuit();

        self::assertTrue($parser->parse($context));

        $node = $context->getContainer()->firstChild();

        self::assertInstanceOf(Text::class, $node);
        self::assertEquals('\\', $node->getContent());
    }

    /**
     * @dataProvider provideNonLaTeXContainers
     */
    public function testDoesNotAlterRegularNodes(AbstractBlock $container): void
    {
        $context = $this->getContext($container, false);

        $parser = new EscapableParserShortCircuit();

        $context
            ->method('getDelimiterStack')
            ->willReturn(new DelimiterStack())
        ;

        self::assertFalse($parser->parse($context));
    }

    public function provideNonLaTeXContainers(): \Generator
    {
        yield 'no LaTeX block or paragraph (1)' => [
            new FencedCode(3, '`', 0),
        ];

        yield 'no LaTeX block or paragraph (2)' => [
            new HtmlBlock(HtmlBlock::TYPE_1_CODE_CONTAINER),
        ];

        yield 'paragraph (no delimiters on stack)' => [
            new Paragraph(),
        ];
    }

    /**
     * @return InlineParserContext&MockObject $context
     */
    private function getContext(AbstractBlock $container, bool $expectChange = true)
    {
        /** @var Cursor&MockObject $cursor */
        $cursor = $this->createMock(Cursor::class);

        $cursor
            ->expects($expectChange ? self::once() : self::never())
            ->method('advanceBy')
            ->with(1)
        ;

        /** @var InlineParserContext&MockObject $context */
        $context = $this->createMock(InlineParserContext::class);

        $context
            ->method('getContainer')
            ->willReturn($container)
        ;

        $context
            ->method('getCursor')
            ->willReturn($cursor)
        ;

        return $context;
    }
}
