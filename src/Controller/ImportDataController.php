<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Form\ImportType;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResult;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class ImportDataController
{
    /** @var FlashBagInterface */
    private $flashBag;

    /** @var ServiceRegistryInterface */
    private $registry;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var \Twig_Environment */
    private $twig;

    public function __construct(
        ServiceRegistryInterface $registry,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        \Twig_Environment $twig
    ) {
        $this->registry = $registry;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->flashBag = $flashBag;
    }

    public function importFormAction(Request $request): Response
    {
        $importer = $request->attributes->get('resource');
        $form = $this->getForm($importer);

        $content = $this->twig->render(
            '@FOSSyliusImportExportPlugin/Crud/import_form.html.twig',
            ['form' => $form->createView(), 'resource' => $importer]
        );

        return new Response($content);
    }

    public function importAction(Request $request): RedirectResponse
    {
        $importer = $request->attributes->get('resource');
        $form = $this->getForm($importer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->importData($importer, $form);
            } catch (\Throwable $exception) {
                $this->flashBag->add('error', $exception->getMessage());
            }
        }
        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer ?? '');
    }

    private function getForm(string $importerType): FormInterface
    {
        return $this->formFactory->create(ImportType::class, null, ['importer_type' => $importerType]);
    }

    private function importData(string $importer, FormInterface $form): void
    {
        $format = $form->get('format')->getData();
        $name = ImporterRegistry::buildServiceName($importer, $format);
        if (!$this->registry->has($name)) {
            $message = sprintf("No importer found of type '%s' for format '%s'", $importer, $format);

            throw new ImporterException($message);
        }

        /** @var UploadedFile|null $file */
        $file = $form->get('import-data')->getData();
        /** @var ImporterInterface $service */
        $service = $this->registry->get($name);

        if (null === $file) {
            throw new ImporterException('No file selected');
        }

        $path = $file->getRealPath();

        if (false === $path) {
            throw new ImporterException(sprintf('File %s could not be loaded', $file->getClientOriginalName()));
        }

        /** @var ImporterResult $result */
        $result = $service->import($path);

        $message = sprintf(
            'Imported via %s importer (Time taken in ms: %s, Imported %s, Skipped %s, Failed %s)',
            $name,
            $result->getDuration(),
            count($result->getSuccessRows()),
            count($result->getSkippedRows()),
            count($result->getFailedRows())
        );

        $this->flashBag->add('success', $message);

        if ($result->getMessage() !== null) {
            throw new ImporterException($result->getMessage());
        }
    }
}
