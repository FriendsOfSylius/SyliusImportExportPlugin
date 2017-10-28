<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

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

    public function __construct(ServiceRegistry $registry, UrlGeneratorInterface $router, Session $session)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->session = $session;
    }

    public function importAction(Request $request): RedirectResponse
    {
        $importType = $request->attributes->get('resource');
        $this->importData($request, $importType);
        return new RedirectResponse($this->router->generate('sylius_admin_'.$importType.'_index'));
    }

    private function importData(Request $request, $importType): void
    {
        if (!$this->registry->has($importType)) {
            $this->session->getFlashBag()->add('error', 'No importer found of type "' . $importType . '""');
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('import-data');
        $service = $this->registry->get($importType);
        $service->import($file->getRealPath());

        $this->session->getFlashBag()->add('success', 'Data successfully imported');
    }
}
