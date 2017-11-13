<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use Sylius\Component\Registry\ServiceRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @param ServiceRegistry $registry
     * @param UrlGeneratorInterface $router
     * @param Session $session
     */
    public function __construct(ServiceRegistry $registry, UrlGeneratorInterface $router, Session $session)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function importAction(Request $request): RedirectResponse
    {
        $importer = $request->attributes->get('resource');
        $format = $request->request->get('format');

        $this->importData($request, $importer, $format);

        return new RedirectResponse($this->router->generate('sylius_admin_' . $importer . '_index'));
    }

    /**
     * @param Request $request
     * @param string $importer
     * @param $format
     */
    private function importData(Request $request, string $importer, string $format): void
    {
        $name = ImporterRegistry::buildServiceName($importer, $format);
        if (!$this->registry->has($name)) {
            $message = sprintf("No importer found of type '%s' for format '%s'", $importer, $format);
            $this->session->getFlashBag()->add('error', $message);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('import-data');
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
