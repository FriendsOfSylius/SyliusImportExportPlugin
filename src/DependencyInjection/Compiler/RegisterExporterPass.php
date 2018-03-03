<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use Sylius\Bundle\UiBundle\Block\BlockEventListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterExporterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceId = 'sylius.exporters_registry';
        if (!$container->has($serviceId)) {
            return;
        }

        $exportersRegistry = $container->findDefinition($serviceId);

        foreach ($container->findTaggedServiceIds('sylius.exporter') as $id => $attributes) {
            if (!isset($attributes[0]['type'])) {
                throw new \InvalidArgumentException('Tagged exporter ' . $id . ' needs to have a type');
            }
            if (!isset($attributes[0]['format'])) {
                throw new \InvalidArgumentException('Tagged exporter ' . $id . ' needs to have a format');
            }
            $type = $attributes[0]['type'];
            $format = $attributes[0]['format'];
            $name = ExporterRegistry::buildServiceName($type, $format);

            $exportersRegistry->addMethodCall('register', [$name, new Reference($id)]);

            if ($container->getParameter('sylius.importer.web_ui')) {
                $this->registerImportFormBlockEvent($container, $type);
            }
        }
    }

    private function registerImportFormBlockEvent(ContainerBuilder $container, string $type): void
    {
        $eventHookName = ExporterRegistry::buildEventHookName($type) . '.export';

        if ($container->has($eventHookName) === false) {
            $container
                ->register(
                    $eventHookName,
                    BlockEventListener::class
                )
                ->setAutowired(false)
                ->addArgument('@FOSSyliusImportExportPlugin/Crud/export.html.twig')
                ->addTag(
                    'kernel.event_listener',
                    [
                        'event' => 'sonata.block.event.sylius.admin.' . $type . '.index.after_content',
                        'method' => 'onBlockEvent',
                    ]
                );
        }
    }
}
