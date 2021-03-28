<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Listener\ExportButtonGridListener;
use Port\Csv\CsvWriter;
use Port\Spreadsheet\SpreadsheetWriter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterExporterPass implements CompilerPassInterface
{
    private const CLASS_CSV_WRITER = CsvWriter::class;
    private const CLASS_SPREADSHEET_WRITER = SpreadsheetWriter::class;

    /** @var array */
    private $typesAndFormats = [];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $serviceId = 'sylius.exporters_registry';
        if ($container->has($serviceId) == false) {
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

            if (null !== $container->getParameter('sylius.exporter.web_ui')) {
                $this->registerTypeAndFormat($type, $format);
            }
        }

        if (null !==  $container->getParameter('sylius.exporter.web_ui') && (null !== $this->typesAndFormats && '' != $this->typesAndFormats)) {
            $this->registerEventListenersForExportButton($container);
        }
    }

    private function registerTypeAndFormat(string $type, string $format): void
    {
        if ('csv' === $format && !class_exists(self::CLASS_CSV_WRITER)) {
            return;
        }

        if ('xlsx' === $format && !class_exists(self::CLASS_SPREADSHEET_WRITER)) {
            return;
        }

        if (!isset($this->typesAndFormats[$type])) {
            $this->typesAndFormats[$type] = [];
        }

        if (!isset($this->typesAndFormats[$type][$format])) {
            $this->typesAndFormats[$type][] = $format;
        }
    }

    private function registerEventListenersForExportButton(ContainerBuilder $container): void
    {
        foreach ($this->typesAndFormats as $type => $formats) {
            $this->registerSingleEventListenerForExportButton($container, $type, $formats);
        }
    }

    /**
     * @param string[] $formats
     */
    private function registerSingleEventListenerForExportButton(ContainerBuilder $container, string $type, array $formats): void
    {
        $eventHookName = ExporterRegistry::buildGridButtonsEventHookName($type, $formats) . '_export';

        if ($container->has($eventHookName)) {
            return;
        }

        if (!$container->has($this->getControllerName($type))) {
            return;
        }

        $container
            ->register(
                $eventHookName,
                ExportButtonGridListener::class
            )
            ->setAutowired(false)
            ->addArgument($type)
            ->addArgument($formats)
            ->addMethodCall('setRequest', [new Reference('request_stack')])
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

    private function getControllerName(string $type): string
    {
        if (strpos($type, '.') !== false) {
            $type = substr($type, strpos($type, '.') + 1);
        }

        return \sprintf('sylius.controller.export_data_%s', $type);
    }
}
