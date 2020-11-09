<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown;

use LiveWorksheet\Parser\Markdown\Converter;
use LiveWorksheet\Parser\Parameter\Parameter;
use LiveWorksheet\Parser\Tests\Fixtures\Markdown\TestInput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Webmozart\PathUtil\Path;

class ConverterTest extends TestCase
{
    /**
     * @dataProvider provideMarkdownToHtmlSamples
     */
    public function testMarkdownToHtml(string $markdown, string $html): void
    {
        $converter = new Converter();

        $input = new TestInput();

        $input->setContent($markdown);

        $input->setParameters([
            new Parameter('Foo', ''),
            new Parameter('Bar', ''),
        ]);

        $input->setResources([
            'res/foo.svg' => '/path/to/res/foo.svg',
            'bar.svg' => '/path/to/bar.svg',
            'index.md' => '/path/to/index.md',
        ]);

        self::assertEquals($html, $converter->markdownToHtml($input));
    }

    public function provideMarkdownToHtmlSamples(): \Generator
    {
        $testFiles = (new Finder())
            ->in(__DIR__.'/../Fixtures/files/markdown')
            ->files()
            ->name('*.md')
        ;

        foreach ($testFiles as $markdownFile) {
            $markdown = file_get_contents(
                $markdownFile->getPathname()
            );

            $name = $markdownFile->getFilenameWithoutExtension();

            $html = file_get_contents(
                Path::join(
                    Path::getDirectory($markdownFile->getPathname()),
                    $name.'.html'
                )
            );

            yield $name => [$markdown, $html];
        }
    }
}
