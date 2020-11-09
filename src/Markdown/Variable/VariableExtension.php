<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\Variable;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use LiveWorksheet\Parser\Markdown\InputContextAwareInterface;
use LiveWorksheet\Parser\Markdown\InputContextInterface;

class VariableExtension implements InputContextAwareInterface, ExtensionInterface
{
    private VariableRenderer $renderer;

    public function __construct(VariableRenderer $renderer = null)
    {
        $this->renderer = $renderer ?? new VariableRenderer();
    }

    public function register(ConfigurableEnvironmentInterface $environment): void
    {
        $environment
            ->addDelimiterProcessor(new VariableDelimiterProcessor())
            ->addInlineRenderer(
                Variable::class,
                $this->renderer
            )
        ;
    }

    public function setInput(?InputContextInterface $input): void
    {
        $this->renderer->setInput($input);
    }
}
