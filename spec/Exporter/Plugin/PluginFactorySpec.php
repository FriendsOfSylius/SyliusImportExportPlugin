<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginFactory;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginFactoryInterface;
use PhpSpec\ObjectBehavior;

class PluginFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PluginFactory::class);
    }

    function it_implements_the_plugin_factory_interface()
    {
        $this->shouldImplement(PluginFactoryInterface::class);
    }
}
