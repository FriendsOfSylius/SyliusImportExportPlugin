<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\Plugin;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginInterface;
use PhpSpec\ObjectBehavior;

class PluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Plugin::class);
    }

    function it_implements_the_plugin_interface()
    {
        $this->shouldImplement(PluginInterface::class);
    }
}
