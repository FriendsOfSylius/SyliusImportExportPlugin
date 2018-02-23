<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

use Sylius\Component\Registry\ServiceRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ExportDataController
 */
final class ExportDataController
{
    /** @var ServiceRegistry */
    private $registry;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var Session */
    private $session;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var \Twig_Environment */
    private $twig;

    /**
     * @param ServiceRegistry $registry
     * @param UrlGeneratorInterface $router
     * @param Session $session
     */
    public function __construct(
        ServiceRegistry $registry,
        UrlGeneratorInterface $router,
        Session $session,
        FormFactoryInterface $formFactory,
        \Twig_Environment $twig
    ) {
        $this->registry = $registry;
        $this->router = $router;
        $this->session = $session;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
    }

    public function exportAction()
    {
        /** @todo implement export-Action */
    }
}
