<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin;

use FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler\RegisterImporterPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SyliusImportExportPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterImporterPass());
    }
}
