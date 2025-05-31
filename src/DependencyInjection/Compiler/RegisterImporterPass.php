<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use Sylius\TwigHooks\Hookable\HookableTemplate;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterImporterPass implements CompilerPassInterface
{
    private $twigHookNames = [
        'taxonomy' => 'sylius_admin.taxon.create.content.sections#right',
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
        $twigHookName = $this->buildTwigHookName($type);
        $twigHookId = sprintf('%s.%s', $twigHookName, $type);

        if ($container->has($twigHookId)) {
            return;
        }

        $templateName = $this->buildTemplateName($type);

        $container
            ->register($twigHookId, HookableTemplate::class)
            ->setArguments([
                $twigHookName,
                $type,
                $templateName,
                [],
                ['resource' => $type],
                null,
                true,
            ])
            ->addTag('sylius_twig_hooks.hookable', ['priority' => 0])
        ;
    }

    private function buildTwigHookName(string $type): string
    {
        if (isset($this->twigHookNames[$type])) {
            return $this->twigHookNames[$type];
        }

        return sprintf('sylius_admin.%s.index.content.grid', $type);
    }

    private function buildTemplateName(string $type): string
    {
        if (isset($this->templateNames[$type])) {
            return $this->templateNames[$type];
        }

        return '@FOSSyliusImportExportPlugin/Crud/import.html.twig';
    }
}
