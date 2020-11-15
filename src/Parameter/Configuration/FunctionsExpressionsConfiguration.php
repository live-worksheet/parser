<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\Configuration;

use LiveWorksheet\Parser\Parameter\Types\FunctionExpressionType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
final class FunctionsExpressionsConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('_functions');

        $treeBuilder->getRootNode()
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->beforeNormalization()
                // Allow defining expressions as strings
                ->always(static fn (array $values) => array_map(
                    static fn ($value): array => \is_array($value) ? $value : ['expr' => $value],
                    $values)
                )
            ->end()
            ->arrayPrototype()
                ->children()
                    ->scalarNode('expr')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->enumNode('compare')
                        ->values(FunctionExpressionType::COMPARE_MODES)
                        ->defaultValue(FunctionExpressionType::COMPARE_MODE_EXACT)
                    ->end()
                    ->integerNode('precision')
                        ->min(0)
                        ->max(10)
                        ->defaultValue(0)
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
