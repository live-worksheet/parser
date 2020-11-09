<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\Image;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use LiveWorksheet\Parser\Markdown\InputContextAwareInterface;
use LiveWorksheet\Parser\Markdown\InputContextInterface;

class ImageExtension implements InputContextAwareInterface, ExtensionInterface
{
    private ImageResourceHandler $handler;

    public function __construct(ImageResourceHandler $handler = null)
    {
        $this->handler = $handler ?? new ImageResourceHandler();
    }

    public function register(ConfigurableEnvironmentInterface $environment): void
    {
        $environment
            ->addEventListener(DocumentParsedEvent::class, [
                $this->handler,
                'onDocumentParsed',
            ])
        ;
    }

    public function setInput(?InputContextInterface $input): void
    {
        $this->handler->setInput($input);
    }
}
