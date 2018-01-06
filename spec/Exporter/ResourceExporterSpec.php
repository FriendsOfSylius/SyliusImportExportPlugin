<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporter;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;
use PhpSpec\ObjectBehavior;

/**
 * Class ResourceExporterSpec
 */
class ResourceExporterSpec extends ObjectBehavior
{
    function let(WriterInterface $writer, PluginPoolInterface $pluginPool)
    {
        $this->beConstructedWith($writer, $pluginPool, ['key1', 'key2']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResourceExporter::class);
    }

    function it_implements_the_resource_exporter_interface()
    {
        $this->shouldImplement(ResourceExporterInterface::class);
    }

    function it_exports_key_value_data_with_1_plugin(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        PluginInterface $plugin
    ) {
        $pluginPool
            ->initPlugins(
                [
                    'id_of_data',
                ]
            )
            ->shouldBeCalledTimes(1);

        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $pluginPool->getDataForId('id_of_data')->willReturn($data);

        $writer
            ->write(
                [
                    'key1',
                    'key2',
                ]
            )
            ->shouldBeCalledTimes(1);
        $writer
            ->write($data)
            ->shouldBeCalled();

        $this->export(['id_of_data']);
    }
}
