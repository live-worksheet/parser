<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Command;

use LiveWorksheet\Parser\Exception\ParserException;
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

    private SheetParser $parser;
    private Filesystem $filesystem;

    public function __construct(SheetParser $parser)
    {
        $this->parser = $parser;
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

        $searchPath = $input->getArgument('path');

        if (!Path::isAbsolute($searchPath)) {
            $searchPath = Path::makeAbsolute($searchPath, getcwd());
        }

        if (!$this->filesystem->exists($searchPath) || !is_dir($searchPath)) {
            $style->error("Path '$searchPath' does not exist.");

            return Command::FAILURE;
        }

        try {
            $sheets = $this->parser->parseAll($searchPath, $searchPath, true);
        } catch (ParserException $exception) {
            $style->error(sprintf('Error parsing: %s', $exception->getMessage()));

            return Command::FAILURE;
        }

        $style->writeln(sprintf('A total of %d sheets have been found.', \count($sheets)));

        $style->success('Everything is looking fine.');

        return Command::SUCCESS;
    }
}
