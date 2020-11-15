<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Parameter\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
final class StaticConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('_static');

        $treeBuilder->getRootNode()
            ->useAttributeAsKey('name', false)
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('value')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->beforeNormalization()
                            ->castToArray()
                        ->end()
                        ->validate()
                            ->ifTrue(fn (array $a): bool => $this->hasNonNumericKey($a))
                                ->thenInvalid("Value '%s' should not be associative.")
                        ->end()
                        ->scalarPrototype()
                            ->cannotBeEmpty()
                          ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function hasNonNumericKey(array $array): bool
    {
        $stringKeysCount = \count(array_filter(array_keys($array), 'is_string'));

        return $stringKeysCount > 0;
    }
}
