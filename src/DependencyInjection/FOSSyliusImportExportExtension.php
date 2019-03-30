<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection;

use Port\Csv\CsvReaderFactory;
use Port\Csv\CsvWriter;
use Port\Spreadsheet\SpreadsheetReaderFactory;
use Port\Spreadsheet\SpreadsheetWriter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FOSSyliusImportExportExtension extends Extension
{
    private const CLASS_CSV_READER = CsvReaderFactory::class;
    private const CLASS_CSV_WRITER = CsvWriter::class;

    private const CLASS_SPREADSHEET_READER = SpreadsheetReaderFactory::class;
    private const CLASS_SPREADSHEET_WRITER = SpreadsheetWriter::class;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setParameter('sylius.importer.web_ui', $config['importer']['web_ui']);
        $container->setParameter('sylius.importer.batch_size', $config['importer']['batch_size']);
        $container->setParameter('sylius.importer.fail_on_incomplete', $config['importer']['fail_on_incomplete']);
        $container->setParameter('sylius.importer.stop_on_failure', $config['importer']['stop_on_failure']);

        $container->setParameter('sylius.exporter.web_ui', $config['exporter']['web_ui']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (class_exists(self::CLASS_CSV_READER)) {
            $loader->load('services_import_csv.yml');
        }

        if (class_exists(self::CLASS_CSV_WRITER)) {
            $loader->load('services_export_csv.yml');
        }

        if (class_exists(self::CLASS_SPREADSHEET_READER)) {
            $loader->load('services_import_spreadsheet.yml');
        }

        if (class_exists(self::CLASS_SPREADSHEET_WRITER) && extension_loaded('zip')) {
            $loader->load('services_export_spreadsheet.yml');
        }

        if (isset($config['message_queue'])) {
            $loader->load('services_message_queue.yml');
            $config['message_queue']['importer_service_id'] = $config['message_queue']['importer_service_id'] ?? $config['message_queue']['service_id'];
            $config['message_queue']['exporter_service_id'] = $config['message_queue']['exporter_service_id'] ?? $config['message_queue']['service_id'];
            $container->setParameter('sylius.message_queue', $config['message_queue']);
        }

        $loader->load('services_import_json.yml');

        $loader->load('services_export_json.yml');
    }
}
