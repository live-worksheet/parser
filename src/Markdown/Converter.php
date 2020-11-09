<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown;

use League\CommonMark\Block\Element\Document;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\Environment as CommonMarkEnvironment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\HtmlRenderer;
use LiveWorksheet\Parser\Markdown\Image\ImageExtension;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXExtension;
use LiveWorksheet\Parser\Markdown\Variable\VariableExtension;

class Converter
{
    private Environment $environment;
    private DocParser $parser;
    private HtmlRenderer $renderer;

    public function __construct()
    {
        $environment = new Environment();

        // Basics
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new DisallowedRawHtmlExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new SmartPunctExtension());

        // Custom extensions
        $environment->addExtension(new LaTeXExtension());
        $environment->addExtension(new VariableExtension());
        $environment->addExtension(new ImageExtension());

        $environment->mergeConfig([
            'renderer' => [
                'block_separator' => "\n",
                'inner_separator' => "\n",
                'soft_break' => "\n",
            ],
            'html_input' => CommonMarkEnvironment::HTML_INPUT_ESCAPE,
            'allow_unsafe_links' => false,
            'max_nesting_level' => 15,
        ]);

        $this->parser = new DocParser($environment);
        $this->renderer = new HtmlRenderer($environment);

        $this->environment = $environment;
    }

    /**
     * Converts markdown to HTML. The context's content will be used as the
     * main resource. Other properties are e.g. used to replace variables or
     * to adjust resource paths.
     */
    public function markdownToHtml(InputContextInterface $input): string
    {
        $document = $this->parse($input);

        $action = fn (): string => $this->renderer->renderBlock($document);

        return $this->executeInContext($action, $input);
    }

    private function parse(InputContextInterface $input): Document
    {
        $content = $input->getContent();

        $action = fn (): Document => $this->parser->parse($content);

        return $this->executeInContext($action, $input);
    }

    /**
     * @template T
     * @psalm-param \Closure():T $action
     * @psalm-return T
     */
    private function executeInContext(\Closure $action, InputContextInterface $input)
    {
        $inputAwareExtensions = array_filter(
            $this->environment->getExtensions(),
            static fn (ExtensionInterface $extension): bool => $extension instanceof InputContextAwareInterface
        );

        // Set context
        /** @var InputContextAwareInterface $extension */
        foreach ($inputAwareExtensions as $extension) {
            $extension->setInput($input);
        }

        // Execute action
        $return = $action();

        // Reset context
        foreach ($inputAwareExtensions as $extension) {
            $extension->setInput(null);
        }

        return $return;
    }
}
