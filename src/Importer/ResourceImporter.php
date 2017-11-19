<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Port\Reader\ReaderFactory;

class ResourceImporter implements ImporterInterface
{
    /** @var ReaderFactory */
    private $readerFactory;

    /** @var ObjectManager */
    private $objectManager;

    /** @var ResourceProcessorInterface */
    private $resourceProcessor;

    /** @var ImporterResultInterface */
    private $result;

    public function __construct(
        ReaderFactory $readerFactory,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult
    ) {
        $this->readerFactory = $readerFactory;
        $this->objectManager = $objectManager;
        $this->resourceProcessor = $resourceProcessor;
        $this->result = $importerResult;
    }

    /**
     * @param string $fileName
     *
     * @return ImporterResultInterface
     */
    public function import(string $fileName): ImporterResultInterface
    {
        $reader = $this->readerFactory->getReader(new \SplFileObject($fileName));

        $this->result->start();
        foreach ($reader as $i => $row) {
            try {
                $this->resourceProcessor->process($row);
                $this->result->success($i);
            } catch (ItemIncompleteException $e) {
                $this->result->skipped($i);
            } catch (ImporterException $e) {
                $this->result->failed($i);
            }
        }
        $this->result->stop();

        $this->objectManager->flush();

        return $this->result;
    }
}
