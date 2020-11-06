<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Sheet;

use LiveWorksheet\Parser\Exception\ParserException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Webmozart\PathUtil\Path;

class SheetParser
{
    public const MAIN_FILE = 'index.md';
    public const PARAMETERS_FILE = 'parameters';

    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Parses a sheet directory and returns a Sheet or null if it could not be
     * parsed. If $throw is set to true an exception is thrown instead.
     */
    public function parse(string $path, string $basePath = null, bool $throwParserException = false): ?Sheet
    {
        if (null !== $basePath) {
            if (!Path::isBasePath($basePath, $path)) {
                throw new \InvalidArgumentException("Path '$basePath' is not a valid base path of '$path'.");
            }

            $name = Path::makeRelative($path, $basePath) ?: Path::getFilename($path);
        } else {
            $name = Path::getFilename($path);
        }

        if (!$this->containsMainFile($path)) {
            if ($throwParserException) {
                throw new ParserException("Main file not found in '$path'.");
            }

            return null;
        }

        $files = (new Finder())
            ->in($path)
            ->files()
            ->ignoreDotFiles(true)
        ;

        $fileMap = [];

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileMap[Path::makeRelative($filePath, $path)] = $filePath;
        }

        $getFileContent = static fn (string $file): string => isset($fileMap[$file]) ?
            file_get_contents($fileMap[$file]) : '';

        // Extract content
        $content = $getFileContent(self::MAIN_FILE);

        // Extract parameters
        $parameters = $getFileContent(self::PARAMETERS_FILE);

        return new Sheet(
            $name,
            $content,
            $parameters,
            $fileMap
        );
    }

    /**
     * Traverses a directory structure that contains sheet directories and
     * parses each of them.
     *
     * Returns a mapping: absolute path to sheet root => Sheet.
     *
     * @return array<string, Sheet>
     */
    public function parseAll(string $searchPath, string $basePath = null, bool $throwParserException = false): array
    {
        $directories = (new Finder())
            ->in($searchPath)
            ->directories()
            ->filter(
                fn (\SplFileInfo $f) => $this->containsMainFile($f->getPathname())
            )
        ;

        $sheetMap = [];

        if (null === $basePath) {
            $basePath = $searchPath;
        }

        foreach ($directories as $directory) {
            $directoryPath = Path::canonicalize($directory->getPathname());
            $sheetMap[$directoryPath] = $this->parse($directoryPath, $basePath, $throwParserException);
        }

        return array_filter($sheetMap);
    }

    private function containsMainFile(string $path): bool
    {
        return $this->filesystem->exists(
            Path::join($path, self::MAIN_FILE)
        );
    }
}
