<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPool;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use PhpSpec\ObjectBehavior;

class PluginPoolSpec extends ObjectBehavior
{
    function let(
        PluginInterface $plugin1,
        PluginInterface $plugin2
    ) {
        $this->beConstructedWith([$plugin1, $plugin2], ['description', 'name']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PluginPool::class);
    }

    function it_implements_the_plugin_pool_interface()
    {
        $this->shouldImplement(PluginPoolInterface::class);
    }

    function it_returns_array_of_plugins_after_creation(
        PluginInterface $plugin1,
        PluginInterface $plugin2
    ) {
        $this
            ->getPlugins()
            ->shouldReturn(
                [
                    $plugin1,
                    $plugin2,
                ]
            );
    }

    function it_inits_plugins_with_ids(
        PluginInterface $plugin1,
        PluginInterface $plugin2
    ) {
        $ids = [
            'id1',
            'id2',
            'id3',
        ];

        $plugin1->getFieldNames()->willReturn(['bla']);
        $plugin2->getFieldNames()->willReturn(['test']);

        $plugin1->init($ids)->shouldBeCalled();
        $plugin2->init($ids)->shouldBeCalled();
        $this->initPlugins($ids);
    }

    function it_gets_correct_data_from_multiple_plugins(
        PluginInterface $plugin1,
        PluginInterface $plugin2
    ) {
        $plugin1
            ->getData('id1', ['description', 'name'])
            ->willReturn(
                [
                    'description' => '',
                    'name' => 'testName',
                ]
            );
        $plugin2
            ->getData('id1', ['description', 'name'])
            ->willReturn(
              [
                  'description' => 'this is a description',
                  'name' => '',
              ]
            );

        $plugin1->getFieldNames()->willReturn(['description', 'name']);
        $plugin2->getFieldNames()->willReturn(['description', 'name']);

        $this->getDataForId('id1')
            ->shouldReturn(
                [
                    'description' => 'this is a description',
                    'name' => 'testName',
                ]
            );
    }

    function it_errors_if_not_all_keys_are_serviced(PluginInterface $plugin1)
    {
        $this->beConstructedWith([$plugin1], ['description', 'name', 'blabla']);

        $plugin1->getFieldNames()->willReturn(['description', 'name']);

        $plugin1
            ->getData('id1', ['description', 'name', 'blabla'])
            ->willReturn(
                [
                    'description' => '',
                    'name' => 'testName',
                ]
            );

        $this->shouldThrow(new \InvalidArgumentException('Not all defined export keys have been found: "blabla". Choose from: ""'))->during('getDataForId', ['id1']);
    }
}
