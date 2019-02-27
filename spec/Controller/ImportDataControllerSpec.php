<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Controller\ImportDataController;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ImportDataControllerSpec extends ObjectBehavior
{
    function let(
        ServiceRegistryInterface $registry,
        FlashBagInterface $session,
        FormFactoryInterface $formFactory,
        \Twig_Environment $twig
    ) {
        $this->beConstructedWith(
            $registry,
            $session,
            $formFactory,
            $twig
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImportDataController::class);
    }
}
