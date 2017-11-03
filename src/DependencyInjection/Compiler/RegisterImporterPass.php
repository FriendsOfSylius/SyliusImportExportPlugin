<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use Sylius\Bundle\UiBundle\Block\BlockEventListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterImporterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceId = 'sylius.importers_registry';
        if (!$container->has($serviceId)) {
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

            $this->registerImportFormBlockEvent($container, $type);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $type
     */
    private function registerImportFormBlockEvent(ContainerBuilder $container, string $type): void
    {
        $eventHookName = ImporterRegistry::buildEventHookName($type);

        if ($container->has($eventHookName) === false) {
            $container
                ->register(
                    $eventHookName,
                    BlockEventListener::class
                )
                ->setAutowired(false)
                ->addArgument('@SyliusImportExportPlugin/Crud/import_form.html.twig')
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
