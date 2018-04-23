<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Controller;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Metadata\Metadata;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class ExportDataController extends Controller
{
    /**
     * @var ServiceRegistryInterface
     */
    private $registry;

    /**
     * @var RequestConfigurationFactoryInterface
     */
    private $requestConfigurationFactory;

    /**
     * @var ResourcesCollectionProviderInterface
     */
    private $resourcesCollectionProvider;

    /**
     * @param ServiceRegistryInterface $registry
     * @param RequestConfigurationFactoryInterface $requestConfigurationFactory
     * @param ResourcesCollectionProviderInterface $resourcesCollectionProvider
     */
    public function __construct(
        ServiceRegistryInterface $registry,
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        ResourcesCollectionProviderInterface $resourcesCollectionProvider
    ) {
        $this->registry = $registry;
        $this->requestConfigurationFactory = $requestConfigurationFactory;
        $this->resourcesCollectionProvider = $resourcesCollectionProvider;
    }

    /**
     * @param string $resource
     * @param string $format
     *
     * @return Response
     */
    public function exportAction(Request $request, string $resource, string $format): Response
    {
        $outputFilename = sprintf('%s-%s.%s', $resource, date('Y-m-d'), $format); // @todo Create a service for this

        return $this->exportData($request, $resource, $format, $outputFilename);
    }

    /**
     * @param Request $request
     * @param string $exporter
     * @param string $format
     * @param string $outputFilename
     *
     * @return Response
     *
     * @throws \Exception
     */
    private function exportData(Request $request, string $exporter, string $format, string $outputFilename): Response
    {
        [$applicationName, $resource] = explode('.', $exporter);
        $metadata = Metadata::fromAliasAndConfiguration($exporter,
            $this->container->getParameter('sylius.resources')[$exporter]);
        $configuration = $this->requestConfigurationFactory->create($metadata, $request);

        $name = ExporterRegistry::buildServiceName($exporter, $format);
        if (!$this->registry->has($name)) {
            throw new \Exception(sprintf("No exporter found of type '%s' for format '%s'", $exporter, $format));
        }
        /** @var ResourceExporterInterface $service */
        $service = $this->registry->get($name);

        $resources = $this->findResources($configuration, $this->findRepository($resource));
        $service->export($this->getResourceIds($resources));

        $response = new Response($service->getExportedData());
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $outputFilename
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @param string $resource
     *
     * @return RepositoryInterface
     *
     * @throws \Exception
     */
    private function findRepository(string $resource): RepositoryInterface
    {
        $repositoryName = sprintf('sylius.repository.%s', $resource);
        if (!$this->has($repositoryName)) {
            throw new \Exception(sprintf("No repository found with id '%s'", $repositoryName));
        }

        /** @var \Sylius\Component\Resource\Repository\RepositoryInterface $repository */
        $repository = $this->get($repositoryName);

        return $repository;
    }

    /**
     * @param ResourceGridView|array $resources
     *
     * @return array
     */
    private function getResourceIds($resources): array
    {
        return array_map(function (ResourceInterface $resource) {
            return $resource->getId();
        }, $this->getResources($resources));
    }

    /**
     * @param ResourceGridView|array $resources
     *
     * @return array
     */
    private function getResources($resources): array
    {
        return is_array($resources) ? $resources : $this->getResourcesItems($resources);
    }

    /**
     * @param ResourceGridView $resources
     *
     * @return array
     */
    private function getResourcesItems(ResourceGridView $resources): array
    {
        $data = $resources->getData();

        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof Pagerfanta) {
            $results = [];

            for ($i = 0; $i < $data->getNbPages(); ++$i) {
                $results = array_merge($results, iterator_to_array($data->getCurrentPageResults()));

                if ($data->hasNextPage()) {
                    $data->getNextPage();
                }
            }

            return $results;
        }
    }

    /**
     * @param RequestConfiguration $configuration
     * @param RepositoryInterface $repository
     *
     * @return ResourceGridView|array
     */
    private function findResources(RequestConfiguration $configuration, RepositoryInterface $repository)
    {
        return $this->resourcesCollectionProvider->get($configuration, $repository);
    }
}
