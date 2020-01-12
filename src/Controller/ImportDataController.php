<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Form\ImportType;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResult;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Metadata\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig\Environment;

final class ImportDataController
{
    /** @var RequestConfigurationFactoryInterface */
    private $configurationFactory;

    /** @var RegistryInterface */
    private $resourceRegistry;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var ServiceRegistryInterface */
    private $registry;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var \Twig_Environment */
    private $twig;

    public function __construct(
        RequestConfigurationFactoryInterface $configurationFactory,
        RegistryInterface $resourceRegistry,
        ServiceRegistryInterface $registry,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        Environment $twig
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->resourceRegistry = $resourceRegistry;
        $this->registry = $registry;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->flashBag = $flashBag;
    }

    public function importAction(Request $request, string $resource): Response
    {
        $configuration = $this->configurationFactory->create($this->resourceRegistry->get($resource), $request);

        $form = $this->formFactory->create(ImportType::class, null, ['importer_type' => $resource]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->import(
                    $resource,
                    $form->get('format')->getData(),
                    $form->get('file')->getData()
                );
            } catch (\Throwable $exception) {
                $this->flashBag->add('error', $exception->getMessage());
            }
        }

        return new Response(
            $this->twig->render(
                '@FOSSyliusImportExportPlugin/import.html.twig',
                [
                    'form' => $form->createView(),
                    'resource' => $resource,
                    'configuration' => $configuration,
                    'metadata' => $configuration->getMetadata(),
                ]
            )
        );
    }

    private function import(string $type, string $format, UploadedFile $file): void
    {
        $name = ImporterRegistry::buildServiceName($type, $format);
        /** @var ImporterInterface $service */
        $service = $this->registry->get($name);
        /** @var string $path */
        $path = $file->getRealPath();
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
