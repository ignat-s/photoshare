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
                ->scalarNode('order_created_from_email')->isRequired()->end()
                ->scalarNode('order_created_to_email')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
