<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\Image;

use League\CommonMark\Block\Element\Document;
use League\CommonMark\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use LiveWorksheet\Parser\Markdown\Image\ImageExtension;
use LiveWorksheet\Parser\Markdown\Image\ImageResourceHandler;
use LiveWorksheet\Parser\Markdown\InputContextAwareInterface;
use LiveWorksheet\Parser\Markdown\InputContextInterface;
use PHPUnit\Framework\TestCase;

class ImageExtensionTest extends TestCase
{
    public function testRegistersEventListener(): void
    {
        $handler = $this->createMock(ImageResourceHandler::class);
        $handler
            ->expects(self::atLeastOnce())
            ->method('onDocumentParsed')
        ;

        (new Environment())
            ->addExtension(new ImageExtension($handler))
            ->dispatch(new DocumentParsedEvent(new Document()))
        ;
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
