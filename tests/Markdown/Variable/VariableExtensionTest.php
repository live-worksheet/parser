<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\Variable;

use IteratorAggregate;
use League\CommonMark\Environment;
use LiveWorksheet\Parser\Markdown\InputContextAwareInterface;
use LiveWorksheet\Parser\Markdown\InputContextInterface;
use LiveWorksheet\Parser\Markdown\Variable\Variable;
use LiveWorksheet\Parser\Markdown\Variable\VariableDelimiterProcessor;
use LiveWorksheet\Parser\Markdown\Variable\VariableExtension;
use LiveWorksheet\Parser\Markdown\Variable\VariableRenderer;
use PHPUnit\Framework\TestCase;

class VariableExtensionTest extends TestCase
{
    public function testRegistersComponents(): void
    {
        $environment = (new Environment())->addExtension(new VariableExtension());

        $delimiterProcessor = $environment
            ->getDelimiterProcessors()
            ->getDelimiterProcessor('{')
        ;

        /** @var IteratorAggregate $inlineRenderers */
        $inlineRenderers = $environment->getInlineRenderersForClass(Variable::class);

        self::assertInstanceOf(VariableDelimiterProcessor::class, $delimiterProcessor);
        self::assertInstanceOf(VariableRenderer::class, iterator_to_array($inlineRenderers)[0]);
    }

    public function testSetsInput(): void
    {
        $input = $this->createMock(InputContextInterface::class);

        $renderer = $this->createMock(VariableRenderer::class);
        $renderer
            ->expects(self::once())
            ->method('setInput')
            ->with($input)
        ;

        $extension = new VariableExtension($renderer);
        $extension->setInput($input);

        self::assertInstanceOf(InputContextAwareInterface::class, $extension);
    }
}
