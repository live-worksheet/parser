<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Sheet;

use LiveWorksheet\Parser\Exception\ParserException;
use LiveWorksheet\Parser\Sheet\SheetParser;
use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;

class SheetParserTest extends TestCase
{
    private const SHEETS_FIXTURE_DIR = __DIR__.'/../Fixtures/files/sheets';

    /**
     * @dataProvider provideDemo1Paths
     */
    public function testParse(string $path, ?string $basePath, string $expectedName): void
    {
        $parser = new SheetParser();

        $sheet = $parser->parse($path, $basePath);

        $expectedFileMap = [
            'index.md' => Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1/index.md'),
            'bar.txt' => Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1/bar.txt'),
            'Resources/foo.svg' => Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1/Resources/foo.svg'),
            'parameters.yaml' => Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1/parameters.yaml'),
        ];

        self::assertNotNull($sheet);
        self::assertEquals($expectedName, $sheet->getFullName());
        self::assertEquals("Hello World\n", $sheet->getContent());
        self::assertEquals($expectedFileMap, $sheet->getResources());
        self::assertEquals("_functions:\n  - X = 4 + Y\n\nY: 100\n", $sheet->getParameterData());
    }

    public function provideDemo1Paths(): \Generator
    {
        $fixtureDir = Path::canonicalize(self::SHEETS_FIXTURE_DIR);
        $fullPath = Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1');

        yield 'full name' => [
            $fullPath,
            $fixtureDir,
            'CategoryA/Demo1',
        ];

        yield 'identical path and base path' => [
            $fullPath,
            $fullPath,
            'Demo1',
        ];

        yield 'omitted base path' => [
            $fullPath,
            null,
            'Demo1',
        ];
    }

    public function testParseReturnsNullIfMainFileIsMissing(): void
    {
        $parser = new SheetParser();

        $sheet = $parser->parse(
            Path::join(self::SHEETS_FIXTURE_DIR, 'Demo4'),
            self::SHEETS_FIXTURE_DIR
        );

        self::assertNull($sheet);
    }

    public function testParseThrowsIfMainFileIsMissing(): void
    {
        $parser = new SheetParser();

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
        $parser = new SheetParser();

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
        $parser = new SheetParser();

        $sheet = $parser->parse(
            Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryB/SubCategory/Demo3'),
            self::SHEETS_FIXTURE_DIR,
        );

        self::assertNotNull($sheet);
        self::assertEquals('', $sheet->getParameterData());
    }

    /**
     * @dataProvider provideSheetRootPaths
     */
    public function testParseAll(string $path, ?string $basePath, array $expectedSheets): void
    {
        $parser = new SheetParser();

        $sheets = $parser->parseAll($path, $basePath);

        self::assertCount(\count($expectedSheets), $sheets);

        foreach ($expectedSheets as $expectedSheet) {
            self::assertEquals(
                $expectedSheet,
                $sheets[Path::join(self::SHEETS_FIXTURE_DIR, $expectedSheet)]->getFullName()
            );
        }
    }

    public function provideSheetRootPaths(): \Generator
    {
        $fixtureDir = Path::canonicalize(self::SHEETS_FIXTURE_DIR);

        $allSheets = [
            'CategoryA/Demo1',
            'CategoryA/Demo2',
            'CategoryB/SubCategory/Demo3',
            'Demo5',
            'Demo6',
        ];

        yield 'all (identical base dir)' => [
            $fixtureDir,
            $fixtureDir,
            $allSheets,
        ];

        yield 'all (omitting base dir)' => [
            $fixtureDir,
            null,
            $allSheets,
        ];

        yield 'only category b' => [
            Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryB'),
            $fixtureDir,
            [
                'CategoryB/SubCategory/Demo3',
            ],
        ];
    }

    public function testParseWithBasePathOutsideOfSearchPath(): void
    {
        $parser = new SheetParser();

        $sheet = $parser->parse(
            Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryB/SubCategory/Demo3'),
            Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryB'),
        );

        self::assertNotNull($sheet);
        self::assertEquals('SubCategory/Demo3', $sheet->getFullName());
    }

    public function testParseAllWithBasePathOutsideOfSearchPath(): void
    {
        $parser = new SheetParser();

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

    public function testParseSingle(): void
    {
        $parser = new SheetParser();

        $sheetDir = Path::join(self::SHEETS_FIXTURE_DIR, 'CategoryA/Demo1');
        $sheets = $parser->parseAll($sheetDir);

        self::assertCount(1, $sheets);

        self::assertEquals(
            'Demo1',
            $sheets[$sheetDir]->getFullName()
        );
    }
}
