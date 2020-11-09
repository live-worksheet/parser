<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\LaTeX;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\Text;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXBlock;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXBlockRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LaTeXBlockRendererTest extends TestCase
{
    public function testRendersVariables(): void
    {
        $latexBlock = new LaTeXBlock(3, 0);
        $latexBlock->appendChild(new Text('foo'));

        /** @var ElementRendererInterface&MockObject $htmlRenderer */
        $htmlRenderer = $this->createMock(ElementRendererInterface::class);
        $htmlRenderer
            ->expects(self::once())
            ->method('renderInlines')
            ->with($latexBlock->children())
            ->willReturn('foo')
        ;

        $renderer = new LaTeXBlockRenderer();

        $output = $renderer->render($latexBlock, $htmlRenderer);

        self::assertInstanceOf(HtmlElement::class, $output);
        self::assertEquals("<div data-LaTeX=\"display\">\nfoo\n</div>", (string) $output);
    }

    public function testThrowsOnInvalidType(): void
    {
        $renderer = new LaTeXBlockRenderer();

        $block = $this->createMock(AbstractBlock::class);
        $htmlRenderer = $this->createMock(ElementRendererInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/Incompatible block type: '\\S+'\\./");

        $renderer->render($block, $htmlRenderer);
    }
}
