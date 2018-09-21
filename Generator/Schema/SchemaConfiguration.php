<?php 

namespace Naldz\Bundle\FixturamaBundle\Fixturama\Schema;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SchemaConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('schema');
        $rootNode
            ->children()
                ->arrayNode('databases')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('dsn')
                            ->end()
                            ->arrayNode('models')
                                ->isRequired()
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('fields')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('type')
                                                        ->isRequired()
                                                        ->cannotBeEmpty()
                                                    ->end()
                                                    ->variableNode('params')
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}