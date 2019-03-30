<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MessageQueuePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $config = $container->getParameter('sylius.message_queue');

        if (isset($config['enabled']) && $config['enabled']) {
            $writerDefinition = $container->getDefinition('sylius.message_queue_writer');
            $writerDefinition->addArgument(new Reference($config['exporter_service_id']));
            $writerDefinition->setAbstract(false);

            $readerDefinition = $container->getDefinition('sylius.message_queue_reader');
            $readerDefinition->addArgument(new Reference($config['importer_service_id']));
            $readerDefinition->setAbstract(false);
        }
    }
}
