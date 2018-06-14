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
    protected $objectManager;

    /** @var ResourceProcessorInterface */
    protected $resourceProcessor;

    /** @var ImporterResultInterface */
    protected $result;

    /** @var int */
    protected $batchSize;

    /** @var bool */
    protected $failOnIncomplete;

    /** @var bool */
    protected $stopOnFailure;

    /** @var int */
    private $batchCount;

    public function __construct(
        ReaderFactory $readerFactory,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        $this->readerFactory = $readerFactory;
        $this->objectManager = $objectManager;
        $this->resourceProcessor = $resourceProcessor;
        $this->result = $importerResult;
        $this->batchSize = $batchSize;
        $this->failOnIncomplete = $failOnIncomplete;
        $this->stopOnFailure = $stopOnFailure;
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

        $this->batchCount = 0;
        foreach ($reader as $i => $row) {
            $breakBool = $this->importData($i, $row);
            if ($breakBool) {
                break;
            }
        }

        if ($this->batchCount) {
            $this->objectManager->flush();
        }

        $this->result->stop();

        return $this->result;
    }

    /**
     * @param int $i
     * @param array $row
     *
     * @return bool
     */
    public function importData($i, $row): bool
    {
        $breakBool = false;

        try {
            $this->resourceProcessor->process($row);
            $this->result->success($i);

            ++$this->batchCount;
            if ($this->batchSize && $this->batchCount === $this->batchSize) {
                $this->objectManager->flush();
                $this->batchCount = 0;
            }
        } catch (ItemIncompleteException $e) {
            if ($this->failOnIncomplete) {
                $this->result->failed($i);
                if ($this->stopOnFailure) {
                    $breakBool = true;
                }
            } else {
                $this->result->skipped($i);
            }
        } catch (ImporterException $e) {
            $this->result->failed($i);
            if ($this->stopOnFailure) {
                $breakBool = true;
            }
        }

        return $breakBool;
    }
}
