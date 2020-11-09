<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\Image;

use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMarkCoreExtension;
use League\CommonMark\HtmlRenderer;
use LiveWorksheet\Parser\Markdown\AbstractInputContext;
use LiveWorksheet\Parser\Markdown\Image\ImageResourceHandler;
use LiveWorksheet\Parser\Markdown\InputContextInterface;
use PHPUnit\Framework\TestCase;

class ImageResourceHandlerTest extends TestCase
{
    /**
     * @dataProvider provideMarkdown
     */
    public function testEnhancesImageNodes(string $markdown, string $expectedOutput, InputContextInterface $input = null): void
    {
        $handler = new ImageResourceHandler();

        if (null !== $input) {
            $handler->setInput($input);
        }

        $environment = (new Environment())
            ->addExtension(new CommonMarkCoreExtension())
            ->addEventListener(DocumentParsedEvent::class,
                [$handler, 'onDocumentParsed']
            )
        ;

        $output = (new HtmlRenderer($environment))->renderBlock(
            (new DocParser($environment))->parse($markdown)
        );

        self::assertStringContainsString($expectedOutput, $output);
    }

    public function provideMarkdown(): \Generator
    {
        yield 'unaltered output if there is no input defined' => [
            '![foo](res/image.svg)',
            '<img src="res/image.svg" alt="foo" />',
        ];

        $input = new class() extends AbstractInputContext {
            // Use default behavior
        };

        $input->setResources([
            'res/foo.svg' => 'path/to/res/foo.svg',
        ]);

        yield 'with input and matching resource' => [
            '![foo](res/foo.svg)',
            '<img src="path/to/res/foo.svg" alt="foo" />',
            $input,
        ];

        yield 'with input and no matching resource (no alt tag)' => [
            '![](res/bar.svg)',
            "!missing image 'res/bar.svg'!",
            $input,
        ];

        yield 'with input and no matching resource (with alt tag)' => [
            '![a portrait of bar](res/bar.svg)',
            "!missing image 'a portrait of bar'!",
            $input,
        ];
    }
}
