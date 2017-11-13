<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
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
     *
     * @return ImporterResult
     */
    public function import(string $fileName): ImporterResult
    {
        $reader = $this->readerFactory->getReader(new \SplFileObject($fileName));

        $this->assertMethods($reader);

        $result = new ImporterResult();
        $result->start();
        foreach ($reader as $i => $row) {
            try {
                $this->resourceProcessor->process($row);
                $result->success($i);
            } catch (ItemIncompleteException $e) {
                $result->skipped($i);
            } catch (ImporterException $e) {
                $result->failed($i);
            }
        }
        $result->stop();

        $this->objectManager->flush();

        return $result;
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
