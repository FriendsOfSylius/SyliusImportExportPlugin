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
    private $eventHookNames = [
        'taxonomy' => 'app.block_event_listener.admin.taxon.create.after_content',
    ];

    private $eventNames = [
        'taxonomy' => 'sonata.block.event.sylius.admin.taxon.create.after_content',
    ];

    private $templateNames = [
        'taxonomy' => '@FOSSyliusImportExportPlugin/Taxonomy/import.html.twig',
    ];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
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
            $domain = $attributes[0]['domain'] ?? null;
            $name = ImporterRegistry::buildServiceName($type, $format);

            $importersRegistry->addMethodCall('register', [$name, new Reference($id)]);

            if ($container->getParameter('sylius.importer.web_ui')) {
                $this->registerImportFormBlockEvent($container, $type, $domain);
            }
        }
    }

    private function registerImportFormBlockEvent(ContainerBuilder $container, string $type, ?string $domain): void
    {
        $eventHookName = $this->buildEventHookName($type);

        if ($container->has($eventHookName)) {
            return;
        }

        $eventName = $this->buildEventName($type, $domain);
        $templateName = $this->buildTemplateName($type);
        $container
            ->register(
                $eventHookName,
                BlockEventListener::class
            )
            ->setAutowired(false)
            ->addArgument($templateName)
            ->addTag(
                'kernel.event_listener',
                [
                    'event' => $eventName,
                    'method' => 'onBlockEvent',
                ]
            )
        ;
    }

    private function buildEventHookName(string $type): string
    {
        if (isset($this->eventHookNames[$type])) {
            return $this->eventHookNames[$type];
        }

        return sprintf('app.block_event_listener.admin.crud.after_content_%s.import', $type);
    }

    private function buildEventName(string $type, ?string $domain): string
    {
        if (isset($this->eventNames[$type])) {
            return $this->eventNames[$type];
        }

        $domain = $domain ?? 'sylius';
        // backward compatibility with the old configuration
        if (count(\explode('.', $type)) === 2) {
            [$domain, $type] = \explode('.', $type);
        }

        return \sprintf(
            'sonata.block.event.%s.admin.%s.index.after_content',
            $domain,
            $type
        );
    }

    private function buildTemplateName(string $type): string
    {
        if (isset($this->templateNames[$type])) {
            return $this->templateNames[$type];
        }

        return '@FOSSyliusImportExportPlugin/Crud/import.html.twig';
    }
}
