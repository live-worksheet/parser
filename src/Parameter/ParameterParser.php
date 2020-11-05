<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter;

use LiveWorksheet\Parser\Exception\ParserException;

class ParameterParser
{
    /**
     * Parses a parameter definition and returns a Parameter or null if it
     * could not be parsed. If $throw is set to true an exception is thrown
     * instead.
     */
    public function parse(string $definition, bool $throwParserException = false): ?Parameter
    {
        // Match "<var> = <expression> [| <mode> [<precision>]]"
        preg_match(
            '/^\s*([a-zA-Z_]+[\w_]*)\s*=\s*([^|]*)(?(?=\|)\|\s*([a-zA-Z]*)\s*(\d?)\s*)$/',
            $definition,
            $matches
        );

        // todo: We might want to be less strict in the regular expression and
        //       execute the strict checks afterwards to allow more verbose
        //       exception messages.

        $name = $matches[1] ?? null;
        $expression = $matches[2] ?? null;
        $mode = strtolower($matches[3] ?? '') ?: null;
        $precision = isset($matches[4]) ? (int) $matches[4] : null;

        if (null === $name || null === $expression) {
            if ($throwParserException) {
                throw new ParserException("Could not parse definition: '$definition'.");
            }

            return null;
        }

        if (null !== $mode) {
            $modes = array_filter(
                (new \ReflectionClass(Parameter::class))->getConstants(),
                static fn (string $constant) => 0 === strpos($constant, 'MODE__'),
                ARRAY_FILTER_USE_KEY
            );

            if (!\in_array($mode, $modes, true)) {
                if ($throwParserException) {
                    throw new ParserException("Invalid constraint: Unknown mode '$mode'.");
                }

                return null;
            }
        }

        return new Parameter(
            $name,
            trim($expression),
            $mode,
            $precision
        );
    }

    /**
     * Parses a parameter file, filtering out comments and empty lines and
     * parsing each remaining definition.
     *
     * Returns a mapping: parameter name => Parameter
     *
     * @return array<string, Parameter>
     */
    public function parseAll(string $content, bool $throw = false): array
    {
        // Split input into lines
        $lines = preg_split('/((\r?\n)|(\r\n?))/', $content);

        $parameterMap = [];

        foreach ($lines as $line) {
            // Ignore empty lines and comments
            if (empty($line) || 0 === strpos(ltrim($line), '#')) {
                continue;
            }

            $parameter = $this->parse($line, $throw);

            if (null !== $parameter) {
                $parameterMap[$parameter->getName()] = $parameter;
            }
        }

        return $parameterMap;
    }
}
