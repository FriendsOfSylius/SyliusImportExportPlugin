<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Form\ExportType;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


final class ExportDataController extends Controller
{
    /**
     * @var ServiceRegistryInterface
     */
    private $registry;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var SessionInterface|Session
     */
    private $session;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param ServiceRegistryInterface $registry
     * @param UrlGeneratorInterface $router
     * @param SessionInterface $session
     * @param FormFactoryInterface $formFactory
     * @param \Twig_Environment $twig
     */
    public function __construct(
        ServiceRegistryInterface $registry,
        UrlGeneratorInterface $router,
        SessionInterface $session,
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
     * @return Response
     */
    public function exportFormAction(Request $request): Response
    {
        $form = $this->getForm();

        $content = $this->twig->render(
            '@FOSSyliusImportExportPlugin/Crud/export_form.html.twig',
            ['form' => $form->createView(), 'resource' => $request->attributes->get('resource')]
        );

        return new Response($content);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function exportAction(Request $request): Response
    {
        $exporter = $request->attributes->get('resource');

        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->exportData($exporter, $form);

            return $response;
            /*return new RedirectResponse(
                $this->router->generate('sylius_admin_' . $exporter . '_index'),
                302,
                $response->headers ? : []
            );*/
        }

        return new RedirectResponse($this->router->generate('sylius_admin_' . $exporter . '_index'));
    }

    /**
     * @return FormInterface
     */
    private function getForm(): FormInterface
    {
        return $this->formFactory->create(ExportType::class);
    }

    /**
     * @param string $exporter
     * @param FormInterface $form
     * @return Response
     */
    private function exportData(string $exporter, FormInterface $form): Response
    {
        $format = $form->get('format')->getData();
        $name = ExporterRegistry::buildServiceName($exporter, $format);
        if (!$this->registry->has($name)) {
            $message = sprintf("No exporter found of type '%s' for format '%s'", $exporter, $format);
            $this->session->getFlashBag()->add('error', $message);
        }

        $file = $form->get('export-file-name')->getData();
        /** @var ResourceExporterInterface $service */
        $service = $this->registry->get($name);

        /** @var \Sylius\Component\Resource\Repository\RepositoryInterface $repository */
        $repository = $this->get('sylius.repository.' . $exporter);

        $service->setExportFile($file);
        $allItems = $repository->findAll();
        $idsToExport = [];
        foreach ($allItems as $item) {
            /** @var ResourceInterface $item */
            $idsToExport[] = $item->getId();
        }
        $service->export($idsToExport);

        $exportedData = $service->getExportedData($file);

        $response = new Response($exportedData);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file . '.' . $format
        );

        $response->headers->set('Content-Disposition', $disposition);

        $message = sprintf(
            'successfully exported via %s exporter',
            $name
        /*'Exported via %s exporter (Time taken in ms: %s, Imported %s, Skipped %s, Failed %s)',
        $name,
        $result->getDuration(),
        count($result->getSuccessRows()),
        count($result->getSkippedRows()),
        count($result->getFailedRows())*/
        );

        $this->session->getFlashBag()->add('success', $message);

        return $response;
    }
}
