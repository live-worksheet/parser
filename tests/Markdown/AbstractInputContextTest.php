<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown;

use LiveWorksheet\Parser\Markdown\AbstractInputContext;
use LiveWorksheet\Parser\Parameter\Types\StaticType;
use PHPUnit\Framework\TestCase;

class AbstractInputContextTest extends TestCase
{
    public function testSetAndGetData(): void
    {
        $input = $this->getMockForAbstractClass(AbstractInputContext::class);

        $input->setContent("test\ncontent\n");

        $input->setResources([
            'res/foo.svg' => 'path/to/res/foo.svg',
        ]);

        $input->setParameters([
            new StaticType('X', 'Y'),
        ]);

        self::assertEquals("test\ncontent\n", $input->getContent());

        self::assertEquals('X', $input->getVariable('X'));
        self::assertEquals('', $input->getVariable('Y'));

        self::assertEquals('X', $input->getVariablePlaceholder('X'));
        self::assertEquals('', $input->getVariablePlaceholder('Y'));

        self::assertEquals('path/to/res/foo.svg', $input->getResourcePath('res/foo.svg'));
        self::assertEquals('', $input->getResourcePath('res/invalid.svg'));
    }
}
