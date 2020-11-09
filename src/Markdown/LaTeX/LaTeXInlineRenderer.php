<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\LaTeX;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class LaTeXInlineRenderer implements InlineRendererInterface
{
    /**
     * Render a LaTeX inline element.
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof LaTeX)) {
            throw new \InvalidArgumentException(sprintf("Incompatible inline type: '%s'.", \get_class($inline)));
        }

        $content = $htmlRenderer->renderInlines($inline->children());

        return new HtmlElement(
            'span',
            ['data-LaTeX' => 'inline'],
            $content,
        );
    }
}
