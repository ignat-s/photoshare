<?php

namespace Phosh\MainBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('phosh_main');

        $rootNode
            ->children()
                ->scalarNode('storage_dir')->isRequired()->end()
                ->scalarNode('thumbs_dir')->isRequired()->end()
                ->arrayNode('required_config_attrs')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
