<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\LaTeX;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;

class LaTeXBlockRenderer implements BlockRendererInterface
{
    /**
     * Render a LaTex display block.
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, bool $inTightList = false)
    {
        if (!($block instanceof LaTeXBlock)) {
            throw new \InvalidArgumentException(sprintf("Incompatible block type: '%s'.", \get_class($block)));
        }

        $content = $htmlRenderer->renderInlines($block->children());

        // Render block in new line
        $content = sprintf("\n%s\n", trim($content, "\n"));

        return new HtmlElement(
            'div',
            ['data-LaTeX' => 'display'],
            $content,
        );
    }
}
