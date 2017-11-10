<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin;

use FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler\RegisterImporterPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class FOSSyliusImportExportPlugin extends Bundle
{
    use SyliusPluginTrait;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterImporterPass());
    }
}
