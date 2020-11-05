<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Sheet;

use LiveWorksheet\Parser\Exception\ParserException;
use LiveWorksheet\Parser\Parameter\Parameter;
use LiveWorksheet\Parser\Parameter\ParameterParser;
use LiveWorksheet\Parser\Sheet\SheetParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;

class SheetParserTest extends TestCase
{
    private const SHEETS_FIXTURE_DIR = __DIR__.'/../Fixtures/sheets';

    /**
     * @testWith [false]
     *           [true]
     */
    public function testParse(bool $throw): void
    {
        $parameter = new Parameter('A', 'foobar');

        /** @var ParameterParser&MockObject $parameterParser */
        $parameterParser = $this->createMock(ParameterParser::class);
        $parameterParser
            ->expects(self::once())
            ->method('parseAll')
            ->with("A = foobar\n", $throw)
            ->willReturn(['A' => $parameter])
        ;

        $parser = $this->getParser($parameterParser);

        $sheet = $parser->parse(
            Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1'),
            self::SHEETS_FIXTURE_DIR,
            $throw
        );

        $expectedFileMap = [
            'index.md' => Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1/index.md'),
            'bar.txt' => Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1/bar.txt'),
            'parameters' => Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1/parameters'),
            'Resources/foo.svg' => Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1/Resources/foo.svg'),
        ];

        self::assertNotNull($sheet);
        self::assertEquals('CategoryA/Demo1', $sheet->getFullName());
        self::assertEquals("Hello World\n", $sheet->getContent());
        self::assertEquals($expectedFileMap, $sheet->getResources());
        self::assertEquals(['A' => $parameter], $sheet->getParameters());
    }

    public function testParseReturnsNullIfMainFileIsMissing(): void
    {
        $parser = $this->getParser();

        $sheet = $parser->parse(
            Path::join(self::SHEETS_FIXTURE_DIR, 'Demo4'),
            self::SHEETS_FIXTURE_DIR
        );

        self::assertNull($sheet);
    }

    public function testParseThrowsIfMainFileIsMissing(): void
    {
        $parser = $this->getParser();

        $this->expectException(ParserException::class);
        $this->expectExceptionMessageMatches("/^Main file not found in '\\S+'.$/");

        $parser->parse(
            Path::join(self::SHEETS_FIXTURE_DIR, 'Demo4'),
            self::SHEETS_FIXTURE_DIR,
            true
        );
    }

    public function testParseThrowsIfBasePathIsInvalid(): void
    {
        $parser = $this->getParser();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/^Path '\\S+' is not a valid base path of '\\S+'\\.$/");

        $parser->parse(
            '/some/path',
            '/some/other/path',
            true
        );
    }

    public function testParseWithoutParameterFile(): void
    {
        /** @var ParameterParser&MockObject $parameterParser */
        $parameterParser = $this->createMock(ParameterParser::class);
        $parameterParser
            ->expects(self::once())
            ->method('parseAll')
            ->with('')
            ->willReturn([])
        ;

        $parser = $this->getParser($parameterParser);

        $parser->parse(
            Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo2'),
            self::SHEETS_FIXTURE_DIR,
        );
    }

    public function testParseAll(): void
    {
        $parser = $this->getParser();

        $sheets = $parser->parseAll(
            self::SHEETS_FIXTURE_DIR,
            self::SHEETS_FIXTURE_DIR
        );

        $expectedSheets = [
            'CategoryA/Demo1',
            'CategoryA/Demo2',
            'CategoryB/SubCategory/Demo3',
        ];

        self::assertCount(\count($expectedSheets), $sheets);

        foreach ($expectedSheets as $expectedSheet) {
            self::assertEquals(
                $expectedSheet,
                $sheets[Path::join(self::SHEETS_FIXTURE_DIR, $expectedSheet)]->getFullName()
            );
        }
    }

    public function testParseAllWithBasePathOutsideOfSearchPath(): void
    {
        $parser = $this->getParser();

        $sheets = $parser->parseAll(
            Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA'),
            self::SHEETS_FIXTURE_DIR
        );

        $expectedSheets = [
            'CategoryA/Demo1',
            'CategoryA/Demo2',
        ];

        self::assertCount(\count($expectedSheets), $sheets);

        foreach ($expectedSheets as $expectedSheet) {
            self::assertEquals(
                $expectedSheet,
                $sheets[Path::join(self::SHEETS_FIXTURE_DIR, $expectedSheet)]->getFullName()
            );
        }
    }

    public function testParseAllPassesOnThrowOption(): void
    {
        /** @var ParameterParser&MockObject $parameterParser */
        $parameterParser = $this->createMock(ParameterParser::class);
        $parameterParser
            ->expects(self::atLeastOnce())
            ->method('parseAll')
            ->with(self::anything(), true)
            ->willReturn(['A' => 'foobar'])
        ;

        $parser = $this->getParser($parameterParser);

        $parser->parseAll(
            self::SHEETS_FIXTURE_DIR,
            self::SHEETS_FIXTURE_DIR,
            true
        );
    }

    private function getParser(ParameterParser $parameterParser = null): SheetParser
    {
        return new SheetParser(
            $parameterParser ?? new ParameterParser()
        );
    }
}
