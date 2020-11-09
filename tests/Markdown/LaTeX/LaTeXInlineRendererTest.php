<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\LaTeX;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Text;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeX;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXInlineRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LaTeXInlineRendererTest extends TestCase
{
    public function testRendersVariables(): void
    {
        $latex = new LaTeX();
        $latex->appendChild(new Text('foo'));

        /** @var ElementRendererInterface&MockObject $htmlRenderer */
        $htmlRenderer = $this->createMock(ElementRendererInterface::class);
        $htmlRenderer
            ->expects(self::once())
            ->method('renderInlines')
            ->with($latex->children())
            ->willReturn('foo')
            ;

        $renderer = new LaTeXInlineRenderer();

        $output = $renderer->render($latex, $htmlRenderer);

        self::assertInstanceOf(HtmlElement::class, $output);
        self::assertEquals('<span data-LaTeX="inline">foo</span>', (string) $output);
    }

    public function testThrowsOnInvalidType(): void
    {
        $renderer = new LaTeXInlineRenderer();

        $inline = $this->createMock(AbstractInline::class);
        $htmlRenderer = $this->createMock(ElementRendererInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/Incompatible inline type: '\\S+'\\./");

        $renderer->render($inline, $htmlRenderer);
    }
}
