<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

abstract class FunctionalTestCase extends TestCase
{
    protected static Filesystem $filesystem;
    protected static string $tempDir;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$filesystem = new Filesystem();
        self::$tempDir = Path::join(
            sys_get_temp_dir(),
            uniqid(basename(Path::normalize(static::class)).'_', true)
        );

        if (!self::$filesystem->exists(self::$tempDir)) {
            self::$filesystem->mkdir(self::$tempDir);
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (self::$filesystem->exists(self::$tempDir)) {
            self::$filesystem->remove(self::$tempDir);
        }
    }
}
