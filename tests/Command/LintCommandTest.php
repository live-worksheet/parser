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
use LiveWorksheet\Parser\Markdown\Converter;
use LiveWorksheet\Parser\Parameter\ParameterParser;
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
     * @dataProvider provideLintSamples
     */
    public function testLint(string $path, string $expectedOutput): void
    {
        $lintCommand = $this->getLintCommand();

        $commandTester = new CommandTester($lintCommand);
        $commandTester->execute(['path' => $path]);

        $output = $commandTester->getDisplay(true);

        self::assertEquals($expectedOutput, $output);
    }

    public function provideLintSamples(): \Generator
    {
        $getFile = static fn (string $name): string => file_get_contents(
            Path::join(__DIR__.'/../Fixtures/files/console-output', "$name.out")
        );

        yield 'valid' => [
            Path::canonicalize(__DIR__.'/../Fixtures/files/sheets/CategoryA/Demo1'),
            $getFile('lint-valid'),
        ];

        yield 'with errors' => [
            Path::canonicalize(__DIR__.'/../Fixtures/files/sheets'),
            $getFile('lint-errors'),
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

    /**
     * @param SheetParser&MockObject|null $sheetParser
     */
    private function getLintCommand($sheetParser = null): LintCommand
    {
        return new LintCommand(
            $sheetParser ?? new SheetParser(),
            new ParameterParser(),
            new Converter()
        );
    }
}
