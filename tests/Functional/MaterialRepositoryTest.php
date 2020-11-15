<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Functional;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;
use Webmozart\PathUtil\Path;

class MaterialRepositoryTest extends FunctionalTestCase
{
    private const REPOSITORY_URL = 'https://github.com/live-worksheet/material-de.git';
    private const SHEET_ROOT = 'sheets';

    /**
     * Download a material repository and run the parser against it.
     */
    public function testLintMaterialRepository(): void
    {
        $this->markAsRisky();

        $repository = Path::join(self::$tempDir, 'repository');

        // Shallow clone material repository
        (new Process([
            'git', 'clone', self::REPOSITORY_URL, '--depth', '1', $repository,
        ]))->mustRun();

        // Execute parser against repository
        $executable = Path::canonicalize(__DIR__.'/../../bin/material-parser.phar');

        $parser = new Process(
            [$executable, 'lint', Path::join($repository, self::SHEET_ROOT)],
        );

        $exitCode = $parser->run();
        $output = $parser->getOutput();
        $numberOfFoundSheets = $this->getNumberOfFoundSheets($output);

        // Show output to ease debugging when running via CI
        fwrite(STDOUT, sprintf(
            "%s\n%s%s\n",
            str_repeat('-', 80),
            $output,
            str_repeat('-', 80))
        );

        self::assertGreaterThan(0, $numberOfFoundSheets, 'found sheets');
        self::assertEquals(Command::SUCCESS, $exitCode, 'exit code');
    }

    private function getNumberOfFoundSheets(string $output): int
    {
        if (1 !== preg_match('/A total of (\d+) sheets have been found\./', $output, $matches)) {
            return 0;
        }

        return (int) $matches[1];
    }
}
