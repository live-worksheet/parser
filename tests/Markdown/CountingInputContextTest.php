<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown;

use LiveWorksheet\Parser\Markdown\CountingInputContext;
use LiveWorksheet\Parser\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class CountingInputContextTest extends TestCase
{
    public function testSetAndGetData(): void
    {
        $input = $this->getInputWithData();

        self::assertEquals("test\ncontent\n", $input->getContent());

        self::assertEquals('X', $input->getVariable('X'));
        self::assertEquals('', $input->getVariable('Y'));

        self::assertEquals('X', $input->getVariablePlaceholder('X'));
        self::assertEquals('', $input->getVariablePlaceholder('Y'));

        self::assertEquals('path/to/res/foo.svg', $input->getResourcePath('res/foo.svg'));
        self::assertEquals('', $input->getResourcePath('res/invalid.svg'));
    }

    public function testStatsRepresentHits(): void
    {
        $input = $this->getInputWithData();

        $input->getVariable('X');
        $input->getVariable('Y');
        $input->getVariable('X');

        $input->getVariablePlaceholder('X');
        $input->getVariablePlaceholder('X');
        $input->getVariablePlaceholder('Y');

        $input->getResourcePath('res/foo.svg');
        $input->getResourcePath('res/foo.svg');
        $input->getResourcePath('res/invalid.svg');

        $stats = $input->getStats();

        self::assertEquals(
            [
                'X' => 2,
            ],
            $stats->getVariablesCounts(),
            'variables'
        );

        self::assertEquals(
            [
                'Y' => 1,
            ],
            $stats->getVariablesCounts(false),
            'invalid variables'
        );

        self::assertEquals(
            [
                'X' => 2,
            ],
            $stats->getVariablePlaceholderCounts(),
            'placeholders'
        );

        self::assertEquals(
            [
                'Y' => 1,
            ],
            $stats->getVariablePlaceholderCounts(false),
            'invalid placeholders'
        );

        self::assertEquals(
            [
                'res/foo.svg' => 2,
            ],
            $stats->getResourcesCounts(), 'resource paths'
        );

        self::assertEquals(
            [
                'res/invalid.svg' => 1,
            ],
            $stats->getResourcesCounts(false),
            'invalid resource paths'
        );

        // Stats should not change after being exported
        $input->getVariable('X');

        self::assertEquals(
            [
                'X' => 2,
            ],
            $stats->getVariablesCounts(),
            'unchanged counts'
        );

        self::assertEquals(
            [
                'X' => 3,
            ],
            $input->getStats()->getVariablesCounts(),
            'updated counts'
        );
    }

    private function getInputWithData(): CountingInputContext
    {
        $input = new CountingInputContext();

        $input->setContent("test\ncontent\n");

        $input->setResources([
            'res/foo.svg' => 'path/to/res/foo.svg',
        ]);

        $input->setParameters([
            new Parameter('X', '1 + 1'),
        ]);

        return $input;
    }
}
