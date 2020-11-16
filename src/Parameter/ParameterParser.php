<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter;

use LiveWorksheet\Parser\Exception\ParserException;
use LiveWorksheet\Parser\Parameter\Configuration\FunctionsExpressionsConfiguration;
use LiveWorksheet\Parser\Parameter\Configuration\StaticConfiguration;
use LiveWorksheet\Parser\Parameter\Types\FunctionExpressionType;
use LiveWorksheet\Parser\Parameter\Types\StaticType;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Exception\ParseException as YamlParserException;
use Symfony\Component\Yaml\Yaml;

class ParameterParser
{
    private Processor $processor;
    private FunctionsExpressionsConfiguration $functionsExpressionsConfiguration;
    private StaticConfiguration $staticConfiguration;

    public function __construct()
    {
        $this->processor = new Processor();
        $this->functionsExpressionsConfiguration = new FunctionsExpressionsConfiguration();
        $this->staticConfiguration = new StaticConfiguration();
    }

    /**
     * Parses YAML and returns the (unprocessed) structure.
     */
    public function getRawStructure(string $yamlContent): array
    {
        try {
            return (array) Yaml::parse($yamlContent);
        } catch (YamlParserException $e) {
            throw new ParserException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Parses a parameter structure and returns an array of parameters with keys
     * being the parameter's names.
     *
     * @return array<string, ParameterInterface>
     */
    public function parseStructure(array $structure): array
    {
        if (empty($structure)) {
            return [];
        }

        $entities = [];

        // Process expressions under '_functions' key
        if (null !== ($rawConfig = $structure['_functions'] ?? null)) {
            foreach ($this->processFunctionsExpressionsConfig($rawConfig) as $config) {
                try {
                    $entities[] = new FunctionExpressionType(
                        $config['expr'],
                        $config['compare'],
                        $config['precision'],
                    );
                } catch (\InvalidArgumentException $e) {
                    throw new ParserException($e->getMessage(), 0, $e);
                }
            }

            unset($structure['_functions']);
        }

        // Process static parameters
        foreach ($this->processStaticConfig($structure) as $staticName => $staticValues) {
            try {
                $entities[] = new StaticType((string) $staticName, ...array_map('strval', $staticValues));
            } catch (\InvalidArgumentException $e) {
                throw new ParserException($e->getMessage(), 0, $e);
            }
        }

        // Build map and ensure names are unique
        $entitiesMap = [];

        foreach ($entities as $entity) {
            $staticName = $entity->getName();

            if (isset($entitiesMap[$staticName])) {
                throw new ParserException("Name '$staticName' cannot appear more than once.");
            }

            $entitiesMap[$staticName] = $entity;
        }

        // Make sure there are no unresolvable/circular dependencies
        $this->ensureResolvableDependencies($entitiesMap);

        return $entitiesMap;
    }

    /**
     * Parses YAML and and returns an array of parameters with keys being the
     * parameter's names.
     *
     * @return array<string, ParameterInterface>
     */
    public function parseYaml(string $yamlContent): array
    {
        return $this->parseStructure(
            $this->getRawStructure($yamlContent)
        );
    }

    /**
     * @param mixed $rawConfig
     */
    private function processFunctionsExpressionsConfig($rawConfig): array
    {
        try {
            return $this->processor->processConfiguration($this->functionsExpressionsConfiguration, [$rawConfig]);
        } catch (InvalidDefinitionException | InvalidConfigurationException $e) {
            throw new ParserException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param mixed $rawConfig
     */
    private function processStaticConfig($rawConfig): array
    {
        // Allow arbitrarily named keys
        $normalizedRawConfig = [];

        foreach ((array) $rawConfig as $key => $value) {
            if (!\is_string($key)) {
                throw new ParserException(sprintf("Static value '%s' must contain a string key.", json_encode([$key => $value])));
            }
            $normalizedRawConfig[] = ['name' => $key, 'value' => $value];
        }

        try {
            $config = $this->processor->processConfiguration($this->staticConfiguration, [$normalizedRawConfig]);
        } catch (InvalidDefinitionException | InvalidConfigurationException $e) {
            throw new ParserException($e->getMessage(), 0, $e);
        }

        return array_map(
            static fn (array $parts): array => $parts['value'],
            $config
        );
    }

    /**
     * @param array<string, ParameterInterface> $parameters
     */
    private function ensureResolvableDependencies(array $parameters): void
    {
        $resolvableParameters = [];

        do {
            $changes = false;

            foreach ($parameters as $name => $parameter) {
                // Keep searching if dependencies can not be resolved yet
                if ($parameter instanceof DependentParameterInterface &&
                    !empty(array_diff($parameter->dependsOn(), $resolvableParameters))) {
                    continue;
                }

                // Mark as resolvable
                $resolvableParameters[] = $name;
                unset($parameters[$name]);

                $changes = true;
            }
        } while ($changes);

        if (!empty($parameters)) {
            throw new ParserException(sprintf("There are unresolvable dependencies in function expression(s) '%s'.", implode("', '", array_keys($parameters))));
        }
    }
}
