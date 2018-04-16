<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection;

use Port\Csv\CsvReaderFactory;
use Port\Csv\CsvWriter;
use Port\Excel\ExcelReaderFactory;
use Port\Excel\ExcelWriter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FOSSyliusImportExportExtension extends Extension
{
    private const CLASS_CSV_READER = CsvReaderFactory::class;
    private const CLASS_CSV_WRITER = CsvWriter::class;

    private const CLASS_EXCEL_READER = ExcelReaderFactory::class;
    private const CLASS_EXCEL_WRITER = ExcelWriter::class;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
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

        if (class_exists(self::CLASS_EXCEL_READER)) {
            $loader->load('services_import_excel.yml');
        }

        if (class_exists(self::CLASS_CSV_WRITER) && extension_loaded('zip')) {
            $loader->load('services_export_excel.yml');
        }
    }
}
