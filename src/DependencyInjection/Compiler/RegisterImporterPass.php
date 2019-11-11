<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Listener\ImportButtonGridListener;
use Sylius\Bundle\UiBundle\Block\BlockEventListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterImporterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $typesWithImportButton = [];
        $serviceId = 'sylius.importers_registry';
        if ($container->has($serviceId) == false) {
            return;
        }

        $importersRegistry = $container->findDefinition($serviceId);

        foreach ($container->findTaggedServiceIds('sylius.importer') as $id => $attributes) {
            if (!isset($attributes[0]['type'])) {
                throw new \InvalidArgumentException('Tagged importer ' . $id . ' needs to have a type');
            }
            if (!isset($attributes[0]['format'])) {
                throw new \InvalidArgumentException('Tagged importer ' . $id . ' needs to have a format');
            }
            $type = $attributes[0]['type'];
            $format = $attributes[0]['format'];
            $name = ImporterRegistry::buildServiceName($type, $format);

            $importersRegistry->addMethodCall('register', [$name, new Reference($id)]);

            if ($container->getParameter('sylius.importer.web_ui') && !in_array($type, $typesWithImportButton)) {
                $typesWithImportButton[] = $type;
                $this->registerImportFormBlockEvent($container, $type);
                $this->registerEventListenerForImportButton($container, $type);
            }
        }
    }

    private function registerImportFormBlockEvent(ContainerBuilder $container, string $type): void
    {
        $eventHookName = ImporterRegistry::buildEventHookName($type) . '.import';

        if ($container->has($eventHookName)) {
            return;
        }

        if (strpos($type, '.') !== false) {
            $type = substr($type, strpos($type, '.') + 1);
        }

        $container
            ->register(
                $eventHookName,
                BlockEventListener::class
            )
            ->setAutowired(false)
            ->addArgument('@FOSSyliusImportExportPlugin/Crud/import.html.twig')
            ->addTag(
                'kernel.event_listener',
                [
                    'event' => 'sonata.block.event.sylius.admin.' . $type . '.index.after_content',
                    'method' => 'onBlockEvent',
                ]
            )
        ;
    }

    private function registerEventListenerForImportButton(ContainerBuilder $container, string $type): void
    {
        $serviceId = sprintf('fos_import_export.event_listener.%s_grid.import_button', $type);

        if ($container->has($serviceId)) {
            return;
        }

        $container
            ->register($serviceId, ImportButtonGridListener::class)
            ->setAutowired(false)
            ->addArgument($type)
            ->addTag(
                'kernel.event_listener',
                [
                    'event' => $this->getEventName($type),
                    'method' => 'onSyliusGridAdmin',
                ]
            );
    }

    private function getEventName(string $type): string
    {
        if (strpos($type, '.') !== false) {
            $type = substr($type, strpos($type, '.') + 1);
        }

        return 'sylius.grid.admin_' . $type;
    }
}
