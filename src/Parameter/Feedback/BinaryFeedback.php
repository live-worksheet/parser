<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\Feedback;

final class BinaryFeedback implements FeedbackInterface
{
    private bool $valid;

    public function __construct(bool $valid)
    {
        $this->valid = $valid;
    }

    public function isCorrect(): bool
    {
        return $this->valid;
    }
}
