<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Markdown;

interface InputContextAwareInterface
{
    public function setInput(?InputContextInterface $input): void;
}
