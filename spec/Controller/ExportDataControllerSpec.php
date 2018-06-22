<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Controller\ExportDataController;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExportDataControllerSpec extends ObjectBehavior
{
    function let(
        ServiceRegistryInterface $registry,
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        ResourcesCollectionProviderInterface $resourcesCollectionProvider
    ) {
        $this->beConstructedWith($registry, $requestConfigurationFactory, $resourcesCollectionProvider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ExportDataController::class);
    }

    function it_implements_the_plugin_pool_interface()
    {
        $this->shouldImplement(Controller::class);
    }
}
