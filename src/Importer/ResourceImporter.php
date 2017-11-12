<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Port\Reader;
use Port\Reader\ReaderFactory;

class ResourceImporter implements ImporterInterface
{
    /** @var ReaderFactory */
    private $readerFactory;

    /** @var ObjectManager */
    private $objectManager;

    /** @var ResourceProcessorInterface */
    private $resourceProcessor;

    public function __construct(
        ReaderFactory $readerFactory,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $this->readerFactory = $readerFactory;
        $this->objectManager = $objectManager;
        $this->resourceProcessor = $resourceProcessor;
    }

    /**
     * @param string $fileName
     */
    public function import(string $fileName): void
    {
        $reader = $this->readerFactory->getReader(new \SplFileObject($fileName));

        $this->assertMethods($reader);

        foreach ($reader as $row) {
            $this->resourceProcessor->process($row);
        }
        $this->objectManager->flush();
    }

    /**
     * @param Reader $reader
     *
     * @throws ImporterException
     */
    protected function assertMethods(Reader $reader)
    {
        if (!method_exists($reader, 'getColumnHeaders')) {
            throw new ImporterException('Missing "getColumnHeaders" method on reader');
        }
    }
}
