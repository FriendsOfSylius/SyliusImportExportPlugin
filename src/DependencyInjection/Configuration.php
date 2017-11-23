<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fos_sylius_import_export');

        $rootNode
            ->children()
                ->arrayNode('importer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('web_ui')->defaultTrue()->end()
                        ->integerNode('batch_size')->defaultFalse()->end()
                        ->booleanNode('fail_on_incomplete')->defaultFalse()->end()
                        ->booleanNode('stop_on_failure')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
