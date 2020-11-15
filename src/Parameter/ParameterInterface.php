<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter;

use LiveWorksheet\Parser\Parameter\Feedback\FeedbackInterface;

interface ParameterInterface
{
    /**
     * Returns the parameter name.
     */
    public function getName(): string;

    /**
     * Returns the evaluated value without formatting.
     *
     * @return mixed
     */
    public function getRawValue(ParameterContextInterface $context);

    /**
     * Compares the parameter against a registered user input.
     */
    public function checkInput(ParameterContextInterface $context): FeedbackInterface;
}
