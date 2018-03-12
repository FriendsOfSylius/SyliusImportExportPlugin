<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Listener\ExportButtonListenerGridListener;
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

            if ($container->getParameter('sylius.exporter.web_ui')) {
                $this->registerEventListenerForExportButton($container, $type, $format);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $type
     */
    private function registerEventListenerForExportButton(ContainerBuilder $container, string $type, string $format): void
    {
        $eventHookName = ExporterRegistry::buildGridButtonsEventHookName($type, $format) . '_export';

        if ($container->has($eventHookName)) {
            return;
        }

        $container
            ->register(
                $eventHookName,
                ExportButtonListenerGridListener::class
            )
            ->setAutowired(false)
            ->addArgument($type)
            ->addArgument($format)
            ->addTag(
                'kernel.event_listener',
                [
                    'event' => 'sylius.grid.admin_' . $type,
                    'method' => 'onSyliusGridAdmin',
                ]
            );
    }
}
