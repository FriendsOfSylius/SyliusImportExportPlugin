<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\PluginPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class OrderResourceExporter extends ResourceExporter
{
    /** @var string[] */
    protected $resourceKeys;

    /** @var WriterInterface */
    protected $writer;

    /** @var PluginPoolInterface */
    protected $pluginPool;

    /** @var TransformerPoolInterface|null */
    protected $transformerPool;

    /**
     * @param string[] $resourceKeys
     */

    /** @var RepositoryInterface */
    private $repository;

    public function __construct(
        WriterInterface           $writer,
        PluginPoolInterface       $pluginPool,
        array                     $resourceKeys,
        ?TransformerPoolInterface $transformerPool,
        RepositoryInterface       $repository
    )
    {
        $this->writer = $writer;
        $this->pluginPool = $pluginPool;
        $this->transformerPool = $transformerPool;
        $this->resourceKeys = $resourceKeys;
        $this->repository = $repository;
    }

    public function export(array $idsToExport): void
    {
        $this->pluginPool->initPlugins($idsToExport);
        $this->writer->write($this->resourceKeys);
        foreach ($idsToExport as $id) {
            $items = $this->repository->findBy(['id' => $id]);
            foreach ($items[0]->getItems() as $item) {
                $this->writeDataForId((string)$item->getId());
            }
        }
    }
}
