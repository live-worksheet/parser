<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Command;

use LiveWorksheet\Parser\Command\LintCommand;
use LiveWorksheet\Parser\Exception\ParserException;
use LiveWorksheet\Parser\Parameter\ParameterParser;
use LiveWorksheet\Parser\Sheet\Sheet;
use LiveWorksheet\Parser\Sheet\SheetParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Webmozart\PathUtil\Path;

class LintCommandTest extends TestCase
{
    public function testCommandName(): void
    {
        $command = $this->getLintCommand();

        self::assertEquals('lint', $command->getName());
    }

    /**
     * @dataProvider provideValidPaths
     */
    public function testLint(string $path, string $searchPath): void
    {
        $sheets = [
            'path/to/sheet/A' => new Sheet('A', 'foo'),
            'path/to/sheet/B' => new Sheet('B', 'bar'),
        ];

        /** @var SheetParser&MockObject $sheetParser */
        $sheetParser = $this->createMock(SheetParser::class);
        $sheetParser
            ->expects(self::once())
            ->method('parseAll')
            ->with($searchPath, $searchPath, true)
            ->willReturn($sheets)
        ;

        $command = $this->getLintCommand($sheetParser);

        $commandTester = new CommandTester($command);

        $result = $commandTester->execute(['path' => $path]);
        $output = $commandTester->getDisplay();

        self::assertEquals(Command::SUCCESS, $result);
        self::assertStringContainsString('A total of 2 sheets have been found.', $output);
    }

    public function provideValidPaths(): \Generator
    {
        $sheetFixtureDir = Path::canonicalize(__DIR__.'/../Fixtures/sheets');

        yield 'absolute' => [
            $sheetFixtureDir,
            $sheetFixtureDir,
        ];

        yield 'relative' => [
            'tests/Fixtures/sheets',
            $sheetFixtureDir,
        ];
    }

    /**
     * @dataProvider provideInvalidPaths
     */
    public function testExecuteHaltsOnInvalidPath(string $path): void
    {
        $command = $this->getLintCommand();

        $commandTester = new CommandTester($command);

        $result = $commandTester->execute(['path' => $path]);
        $output = preg_replace('/\s+/', ' ', $commandTester->getDisplay(true));

        self::assertEquals(Command::FAILURE, $result);
        self::assertMatchesRegularExpression("/Path '\\S+' does not exist\\./", $output);
    }

    public function provideInvalidPaths(): \Generator
    {
        yield 'not existing (absolute)' => [
            Path::join(__DIR__, 'foo'),
        ];

        yield 'not existing (relative)' => [
            './foo',
        ];

        yield 'not a folder (absolute)' => [
            __FILE__,
        ];

        yield 'not a folder (relative)' => [
            Path::getFilename(__FILE__),
        ];
    }

    public function testReportsFailuresWhenParsingSheets(): void
    {
        /** @var SheetParser&MockObject $sheetParser */
        $sheetParser = $this->createMock(SheetParser::class);
        $sheetParser
            ->method('parseAll')
            ->willThrowException(new ParserException('<message>'))
        ;

        $command = $this->getLintCommand($sheetParser);

        $commandTester = new CommandTester($command);

        $result = $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        self::assertEquals(Command::FAILURE, $result);
        self::assertStringContainsString('Error parsing sheets: <message>', $output);
    }

    public function testReportsFailuresWhenParsingParameters(): void
    {
        $sheets = [
            'path/to/sheet/A' => new Sheet('A', '', "foo = bar || invalid\n"),
            'path/to/sheet/B' => new Sheet('B', ''),
        ];

        /** @var SheetParser&MockObject $sheetParser */
        $sheetParser = $this->createMock(SheetParser::class);
        $sheetParser
            ->method('parseAll')
            ->willReturn($sheets)
        ;

        /** @var ParameterParser&MockObject $parameterParser */
        $parameterParser = $this->createMock(ParameterParser::class);
        $parameterParser
            ->method('parseAll')
            ->willThrowException(new ParserException('<message>'))
        ;

        $command = $this->getLintCommand($sheetParser, $parameterParser);

        $commandTester = new CommandTester($command);

        $result = $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        self::assertEquals(Command::FAILURE, $result);
        self::assertStringContainsString("Error parsing parameters of sheet 'A': <message>", $output);
    }

    /**
     * @param SheetParser&MockObject|null     $sheetParser
     * @param ParameterParser&MockObject|null $parameterParser
     */
    private function getLintCommand($sheetParser = null, $parameterParser = null): LintCommand
    {
        if (null === $sheetParser) {
            /** @var SheetParser&MockObject $sheetParser */
            $sheetParser = $this->createMock(SheetParser::class);
        }

        if (null === $parameterParser) {
            /** @var ParameterParser&MockObject $parameterParser */
            $parameterParser = $this->createMock(ParameterParser::class);
        }

        return new LintCommand($sheetParser, $parameterParser);
    }
}
