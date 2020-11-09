<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\LaTeX;

use IteratorAggregate;
use League\CommonMark\Environment;
use LiveWorksheet\Parser\Markdown\Image\ImageExtension;
use LiveWorksheet\Parser\Markdown\Image\ImageResourceHandler;
use LiveWorksheet\Parser\Markdown\InputContextAwareInterface;
use LiveWorksheet\Parser\Markdown\InputContextInterface;
use LiveWorksheet\Parser\Markdown\LaTeX\EscapableParserShortCircuit;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeX;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXBlock;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXBlockParser;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXBlockRenderer;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXDelimiterProcessor;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXExtension;
use LiveWorksheet\Parser\Markdown\LaTeX\LaTeXInlineRenderer;
use PHPUnit\Framework\TestCase;

class LaTeXExtensionTest extends TestCase
{
    public function testRegistersComponents(): void
    {
        $environment = (new Environment())->addExtension(new LaTeXExtension());

        $delimiterProcessor = $environment
            ->getDelimiterProcessors()
            ->getDelimiterProcessor('$')
        ;

        /** @var IteratorAggregate $inlineRenderers */
        $inlineRenderers = $environment->getInlineRenderersForClass(LaTeX::class);

        /** @var IteratorAggregate $blockRenderers */
        $blockRenderers = $environment->getBlockRenderersForClass(LaTeXBlock::class);

        /** @var IteratorAggregate $blockParsers */
        $blockParsers = $environment->getBlockParsers();

        /** @var IteratorAggregate $inlineParsers */
        $inlineParsers = $environment->getInlineParsersForCharacter('\\');

        self::assertInstanceOf(LaTeXDelimiterProcessor::class, $delimiterProcessor);
        self::assertInstanceOf(LaTeXInlineRenderer::class, iterator_to_array($inlineRenderers)[0]);

        self::assertInstanceOf(LaTeXBlockParser::class, iterator_to_array($blockParsers)[0]);
        self::assertInstanceOf(LaTeXBlockRenderer::class, iterator_to_array($blockRenderers)[0]);

        self::assertInstanceOf(EscapableParserShortCircuit::class, iterator_to_array($inlineParsers)[0]);
    }

    public function testSetsInput(): void
    {
        $input = $this->createMock(InputContextInterface::class);

        $handler = $this->createMock(ImageResourceHandler::class);
        $handler
            ->expects(self::once())
            ->method('setInput')
            ->with($input)
        ;

        $extension = new ImageExtension($handler);
        $extension->setInput($input);

        self::assertInstanceOf(InputContextAwareInterface::class, $extension);
    }
}
