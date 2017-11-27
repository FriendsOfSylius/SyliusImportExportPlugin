<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginFactoryInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPool;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use PhpSpec\ObjectBehavior;

class PluginPoolSpec extends ObjectBehavior
{
    function let(PluginFactoryInterface $pluginFactory, PluginInterface $plugin)
    {
        $pluginFactory->create('namespace/of/plugin')->willReturn($plugin);
        $this->beConstructedWith($pluginFactory, ['namespace/of/plugin']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PluginPool::class);
    }

    function it_implements_the_plugin_pool_interface()
    {
        $this->shouldImplement(PluginPoolInterface::class);
    }

    function it_returns_array_of_plugins_after_creation(PluginInterface $plugin)
    {
        $this->getPlugins()->shouldReturn([$plugin]);
    }

    function it_can_get_ids_for_plugin_initialisation(PluginInterface $plugin)
    {
        $ids = [
            'id1',
            'id2',
            'id3',
        ];
        $plugin->init($ids)->shouldBeCalled();
        $this->initPlugins($ids);
    }
}
