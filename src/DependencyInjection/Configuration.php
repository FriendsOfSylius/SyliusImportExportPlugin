<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fos_sylius_import_export');

        $rootNode
            ->children()
                ->arrayNode('importer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('web_ui')->defaultTrue()->end()
                        ->integerNode('batch_size')->defaultValue(100)->end()
                        ->booleanNode('fail_on_incomplete')->defaultFalse()->end()
                        ->booleanNode('stop_on_failure')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('exporter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('web_ui')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('message_queue')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('service_id')->defaultNull()->end()
                        ->scalarNode('importer_service_id')->defaultNull()->end()
                        ->scalarNode('exporter_service_id')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
