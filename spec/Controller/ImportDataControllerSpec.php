<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Controller\ImportDataController;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Metadata\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ImportDataControllerSpec extends ObjectBehavior
{
    function let(
        RequestConfigurationFactoryInterface $configurationFactory,
        RegistryInterface $resourceRegistry,
        ServiceRegistryInterface $registry,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        \Twig_Environment $twig
    ) {
        $this->beConstructedWith(
            $configurationFactory,
            $resourceRegistry,
            $registry,
            $flashBag,
            $formFactory,
            $twig
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImportDataController::class);
    }
}
