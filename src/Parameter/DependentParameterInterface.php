<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter;

interface DependentParameterInterface
{
    /**
     * Returns a list of parameters this parameter depends on.
     *
     * @return array<string>
     */
    public function dependsOn(): array;
}
