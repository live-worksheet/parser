<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Command;

use LiveWorksheet\Parser\Exception\ParserException;
use LiveWorksheet\Parser\Parameter\ParameterParser;
use LiveWorksheet\Parser\Sheet\SheetParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

final class LintCommand extends Command
{
    protected static $defaultName = 'lint';

    private SheetParser $sheetParser;
    private ParameterParser $parameterParser;
    private Filesystem $filesystem;

    public function __construct(SheetParser $sheetParser, ParameterParser $parameterParser)
    {
        $this->sheetParser = $sheetParser;
        $this->parameterParser = $parameterParser;
        $this->filesystem = new Filesystem();

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'The sheet source directory.', '.')
            ->setDescription('Check if all sheet data can be parsed successfully.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        /** @var string $searchPath */
        $searchPath = $input->getArgument('path');

        if (!Path::isAbsolute($searchPath)) {
            $searchPath = Path::makeAbsolute($searchPath, getcwd());
        }

        if (!$this->filesystem->exists($searchPath) || !is_dir($searchPath)) {
            $style->error("Path '$searchPath' does not exist.");

            return Command::FAILURE;
        }

        // Parse sheets
        try {
            $sheets = $this->sheetParser->parseAll($searchPath, $searchPath, true);
        } catch (ParserException $exception) {
            $style->error(
                sprintf(
                    'Error parsing sheets: %s',
                    $exception->getMessage()
                )
            );

            return Command::FAILURE;
        }

        // Parse parameters
        foreach ($sheets as $sheet) {
            try {
                $this->parameterParser->parseAll($sheet->getParameters());
            } catch (ParserException $exception) {
                $style->error(
                    sprintf(
                        "Error parsing parameters of sheet '%s': %s",
                        $sheet->getFullName(),
                        $exception->getMessage()
                    )
                );

                return Command::FAILURE;
            }
        }

        $style->writeln(sprintf('A total of %d sheets have been found.', \count($sheets)));

        $style->success('Everything is looking fine.');

        return Command::SUCCESS;
    }
}
