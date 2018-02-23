<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Form\ImportType;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use Sylius\Component\Registry\ServiceRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ImportDataController
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

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function importFormAction(Request $request): Response
    {
        $form = $this->getForm();

        $content = $this->twig->render(
            '@FOSSyliusImportExportPlugin/Crud/import_form.html.twig',
            ['form' => $form->createView(), 'resource' => $request->attributes->get('resource')]
        );

        return new Response($content);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function importAction(Request $request): RedirectResponse
    {
        $importer = $request->attributes->get('resource');
        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->importData($importer, $form);
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
     * @return FormInterface
     */
    private function getForm()
    {
        return $this->formFactory->create(ImportType::class);
    }

    /**
     * @param string $importer
     * @param FormInterface $form
     */
    private function importData(string $importer, FormInterface $form): void
    {
        $format = $form->get('format')->getData();
        $name = ImporterRegistry::buildServiceName($importer, $format);
        if (!$this->registry->has($name)) {
            $message = sprintf("No importer found of type '%s' for format '%s'", $importer, $format);
            $this->session->getFlashBag()->add('error', $message);
        }

        /** @var UploadedFile $file */
        $file = $form->get('import-data')->getData();
        /** @var ImporterInterface $service */
        $service = $this->registry->get($name);
        $result = $service->import($file->getRealPath());

        $message = sprintf(
            'Imported via %s importer (Time taken in ms: %s, Imported %s, Skipped %s, Failed %s)',
            $name,
            $result->getDuration(),
            count($result->getSuccessRows()),
            count($result->getSkippedRows()),
            count($result->getFailedRows())
        );

        $this->session->getFlashBag()->add('success', $message);
    }
}
