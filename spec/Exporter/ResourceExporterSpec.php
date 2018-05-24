<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporter;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler\DateTimeToStringHandler;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Pool;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

/**
 * Class ResourceExporterSpec
 */
class ResourceExporterSpec extends ObjectBehavior
{
    function let(WriterInterface $writer, PluginPoolInterface $pluginPool)
    {
        $this->beConstructedWith($writer, $pluginPool, ['key1', 'key2'], null);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResourceExporter::class);
    }

    function it_implements_the_resource_exporter_interface()
    {
        $this->shouldImplement(ResourceExporterInterface::class);
    }

    function it_should_export_but_also_transform(
        WriterInterface $writer,
        PluginPoolInterface $pluginPool,
        PluginInterface $plugin
    ) {
        $generator = new RewindableGenerator(function () {
            return [new DateTimeToStringHandler()];
        }, $count = 1);

        $pool = new Pool($generator);

        $this->beConstructedWith($writer, $pluginPool, ['key1', 'key2', 'key3'], $pool);

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
            'key3' => \DateTime::createFromFormat('Y-m-d H:i:s', '2018-01-01 13:02:26'),
        ];

        $pluginPool->getDataForId('id_of_data')->willReturn($data);

        $writer
            ->write(
                [
                    'key1',
                    'key2',
                    'key3',
                ]
            )
            ->shouldBeCalledTimes(1);

        $dataTransformed = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => '2018-01-01 13:02:26',
        ];

        $writer
            ->write($dataTransformed)
            ->shouldBeCalled();

        $this->export(['id_of_data']);
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
