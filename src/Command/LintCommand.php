<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Command;

use LiveWorksheet\Parser\Exception\EvaluationException;
use LiveWorksheet\Parser\Exception\ParserException;
use LiveWorksheet\Parser\Markdown\Converter;
use LiveWorksheet\Parser\Markdown\CountingInputContext;
use LiveWorksheet\Parser\Parameter\ParameterInterface;
use LiveWorksheet\Parser\Parameter\ParameterParser;
use LiveWorksheet\Parser\Parameter\ParameterTestContext;
use LiveWorksheet\Parser\Sheet\Sheet;
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
    private Converter $markdownConverter;
    private Filesystem $filesystem;

    public function __construct(SheetParser $sheetParser, ParameterParser $parameterParser, Converter $markdownConverter)
    {
        $this->sheetParser = $sheetParser;
        $this->parameterParser = $parameterParser;
        $this->markdownConverter = $markdownConverter;
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
        $io = new SymfonyStyle($input, $output);

        /** @var string $searchPath */
        $searchPath = $input->getArgument('path');

        if (!Path::isAbsolute($searchPath)) {
            $searchPath = Path::makeAbsolute($searchPath, getcwd());
        }

        if (!$this->filesystem->exists($searchPath) || !is_dir($searchPath)) {
            $io->error("Path '$searchPath' does not exist.");

            return Command::FAILURE;
        }

        // Parse sheets
        $io->text('Reading sheets…');

        try {
            $sheets = $this->sheetParser->parseAll($searchPath, null, true);
        } catch (ParserException $exception) {
            $io->error(sprintf('Error parsing sheets: %s', $exception->getMessage()));

            return Command::FAILURE;
        }

        $io->text(sprintf("A total of %d sheets have been found.\n", \count($sheets)));

        // Validate sheets
        $io->text('Validating sheets…');

        $invalidSheets = [];

        $progressBar = $io->createProgressBar(\count($sheets));
        $progressBar->start();

        foreach ($sheets as $sheet) {
            $progressBar->advance();

            $parameters = [];

            $err1 = $this->parseParameters($sheet, $parameters);
            $err2 = $this->validateParameters($parameters);
            $err3 = $this->validateMarkdown($sheet, $parameters);

            if (!empty($errors = [...$err1, ...$err2, ...$err3])) {
                $invalidSheets[$sheet->getFullName()] = $errors;
            }
        }

        $progressBar->finish();
        $io->writeln("\n");

        if (empty($invalidSheets)) {
            $io->success('Everything is looking fine.');

            return Command::SUCCESS;
        }

        foreach ($invalidSheets as $sheet => $errors) {
            $io->error(
                sprintf("Sheet '%s' contains at least %d error(s):\n%s",
                    $sheet,
                    \count($errors),
                    implode("\n", $errors)
                )
            );
        }

        return Command::FAILURE;
    }

    /**
     * @param array<string, ParameterInterface> $parameters
     */
    private function parseParameters(Sheet $sheet, array &$parameters): array
    {
        try {
            $structure = $this->parameterParser->getRawStructure($sheet->getParameterData());
        } catch (ParserException $exception) {
            return [
                sprintf(" - Bad 'parameters.yaml' format:\n     %s", $exception->getMessage()),
            ];
        }

        try {
            $parameters = $this->parameterParser->parseStructure($structure);
        } catch (ParserException $exception) {
            $parameters = [];

            return [
                sprintf(" - Invalid parameter data:\n     %s", $exception->getMessage()),
            ];
        }

        return [];
    }

    /**
     * @param array<string, ParameterInterface> $parameters
     */
    private function validateParameters(array $parameters): array
    {
        try {
            $context = new ParameterTestContext($parameters);

            foreach ($parameters as $parameter) {
                $parameter->getRawValue($context);
            }
        } catch (EvaluationException $exception) {
            return [
                sprintf(" - Test run for parameters failed:\n     %s", $exception->getMessage()),
            ];
        }

        return [];
    }

    private function validateMarkdown(Sheet $sheet, ?array $parameters): array
    {
        $context = new CountingInputContext();

        $context->setContent($sheet->getContent());
        $context->setResources($sheet->getResources());
        $context->setParameters($parameters ?? []);

        $this->markdownConverter->markdownToHtml($context);

        // Check stats for errors
        $stats = $context->getStats();

        $check = [
            'variable' => $stats->getVariablesCounts(false),
            'variable placeholder' => $stats->getVariablePlaceholderCounts(false),
            'resource' => $stats->getResourcesCounts(false),
        ];

        $errorOutputs = [];

        foreach ($check as $label => $errors) {
            if (empty($errors)) {
                continue;
            }

            foreach ($errors as $value => $count) {
                $errorOutputs[] = sprintf(
                    " - Invalid md: The %s '%s' does not exist (seen %dx).",
                    $label, $value, $count
                );
            }
        }

        return $errorOutputs;
    }
}
