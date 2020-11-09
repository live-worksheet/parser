<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\Variable;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use LiveWorksheet\Parser\Markdown\InputContextAwareInterface;
use LiveWorksheet\Parser\Markdown\InputContextInterface;

class VariableRenderer implements InputContextAwareInterface, InlineRendererInterface
{
    private ?InputContextInterface $input = null;

    public function setInput(?InputContextInterface $input): void
    {
        $this->input = $input;
    }

    /**
     * Render a Variable inline element.
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof Variable)) {
            throw new \InvalidArgumentException(sprintf("Incompatible inline type: '%s'.", \get_class($inline)));
        }

        $name = $inline->getName();

        if (null === $this->input) {
            return '';
        }

        $output = $inline->isInput() ?
            $this->input->getVariablePlaceholder($name) :
            $this->input->getVariable($name);

        return $output ?: "!unknown variable '$name'!";
    }
}
