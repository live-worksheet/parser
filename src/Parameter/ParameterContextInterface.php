<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter;

interface ParameterContextInterface
{
    /**
     * Returns the parameter with the given name.
     */
    public function getParameter(string $name): ParameterInterface;

    /**
     * Returns the current random seed.
     */
    public function getSeed(): int;

    /**
     * Returns the user input registered for this parameter or null if not
     * applicable.
     *
     * @return mixed
     */
    public function getUserInput(string $name);
}
