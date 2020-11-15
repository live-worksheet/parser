<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Parameter\Feedback;

use LiveWorksheet\Parser\Parameter\Feedback\BinaryFeedback;
use PHPUnit\Framework\TestCase;

class BinaryFeedbackTest extends TestCase
{
    public function testIsCorrect(): void
    {
        self::assertTrue((new BinaryFeedback(true))->isCorrect());
        self::assertFalse((new BinaryFeedback(false))->isCorrect());
    }
}
