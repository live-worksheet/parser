<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown\Image;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Text;
use LiveWorksheet\Parser\Markdown\InputContextAwareInterface;
use LiveWorksheet\Parser\Markdown\InputContextInterface;

class ImageResourceHandler implements InputContextAwareInterface
{
    private ?InputContextInterface $input = null;

    public function setInput(?InputContextInterface $input): void
    {
        $this->input = $input;
    }

    /**
     * Adjust image urls based on the defined resource mapping.
     */
    public function onDocumentParsed(DocumentParsedEvent $documentParsedEvent): void
    {
        $document = $documentParsedEvent->getDocument();

        $walker = $document->walker();
        $invalidResources = [];

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if (null === $this->input || !($node instanceof Image) || !$event->isEntering()) {
                continue;
            }

            // Retrieve real image path
            $url = $node->getUrl();
            $path = $this->input->getResourcePath($url);

            if ('' === $path) {
                $invalidResources[] = $node;

                continue;
            }

            $node->setUrl($path);
        }

        foreach ($invalidResources as $node) {
            $node->replaceWith($this->getErrorNode($node));
        }
    }

    private function getErrorNode(Image $node): Text
    {
        $altNode = $node->firstChild();

        $ref = $altNode instanceof Text ?
            $altNode->getContent() : $node->getUrl();

        return new Text("!missing image '$ref'!");
    }
}
