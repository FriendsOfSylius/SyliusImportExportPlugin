<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

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

    public function __construct(ServiceRegistry $registry, UrlGeneratorInterface $router, Session $session)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->session = $session;
    }

    public function importAction(Request $request): RedirectResponse
    {
        $importer = $request->attributes->get('resource');
        $format = $request->attributes->get('format');

        $this->importData($request, $importer, $format);

        return new RedirectResponse($this->router->generate('sylius_admin_'.$importer.'_index'));
    }

    private function importData(Request $request, $importer, $format): void
    {
        $name = ImporterRegistry::buildServiceName($importer, $format);
        if (!$this->registry->has($name)) {
            $message = sprintf("No importer found of type '%s' for format '%s'", $importer, $format);
            $this->session->getFlashBag()->add('error', $message);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('import-data');
        $service = $this->registry->get($name);
        $service->import($file->getRealPath());

        $this->session->getFlashBag()->add('success', 'Data successfully imported');
    }
}
