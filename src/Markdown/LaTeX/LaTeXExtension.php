<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\LaTeX;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;

class LaTeXExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment): void
    {
        $environment
            // LaTeX display blocks ($$$ \n foo \n ... \n $$$)
            ->addBlockParser(new LaTeXBlockParser())
            ->addBlockRenderer(LaTeXBlock::class, new LaTeXBlockRenderer())

            // Inline LaTeX ($$ foo $$)
            ->addDelimiterProcessor(new LaTeXDelimiterProcessor())
            ->addInlineRenderer(LaTeX::class, new LaTeXInlineRenderer())

            // Suppress escaping in LaTeX context
            // Needs to be higher than core EscapableParser (80)
            ->addInlineParser(new EscapableParserShortCircuit(), 85)
        ;
    }
}
