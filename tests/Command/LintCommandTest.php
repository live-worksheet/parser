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
            new Sheet('A', 'foo', [], []),
            new Sheet('B', 'bar', [], []),
        ];

        /** @var SheetParser&MockObject $parser */
        $parser = $this->createMock(SheetParser::class);
        $parser
            ->expects(self::once())
            ->method('parseAll')
            ->with($searchPath, $searchPath, true)
            ->willReturn($sheets)
        ;

        $command = $this->getLintCommand($parser);

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
        $output = $commandTester->getDisplay();

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

    public function testReportsFailures(): void
    {
        /** @var SheetParser&MockObject $parser */
        $parser = $this->createMock(SheetParser::class);
        $parser
            ->method('parseAll')
            ->willThrowException(new ParserException('<message>'))
        ;

        $command = $this->getLintCommand($parser);

        $commandTester = new CommandTester($command);

        $result = $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        self::assertEquals(Command::FAILURE, $result);
        self::assertStringContainsString('Error parsing: <message>', $output);
    }

    /**
     * @param SheetParser&MockObject|null $parser
     */
    private function getLintCommand($parser = null): LintCommand
    {
        if (null === $parser) {
            /** @var SheetParser&MockObject $parser */
            $parser = $this->createMock(SheetParser::class);
        }

        return new LintCommand($parser);
    }
}
