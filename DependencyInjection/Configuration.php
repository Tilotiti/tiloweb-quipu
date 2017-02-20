<?php
/**
 * Created by PhpStorm.
 * User: tilotiti
 * Date: 20/02/2017
 * Time: 17:15
 */

namespace Tiloweb\QuipuBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root("quipu");

        $rootNode
            ->children()
                ->scalarNode('app')->end()
                ->scalarNode('secret')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}