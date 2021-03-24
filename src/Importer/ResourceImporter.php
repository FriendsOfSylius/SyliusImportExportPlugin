<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\ORM\EntityManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use Port\Reader\ReaderFactory;

class ResourceImporter implements ImporterInterface
{
    /** @var ReaderFactory */
    private $readerFactory;

    /** @var EntityManager */
    protected $entityManager;

    /** @var ResourceProcessorInterface */
    protected $resourceProcessor;

    /** @var ImportResultLoggerInterface */
    protected $result;

    /** @var int */
    protected $batchSize;

    /** @var bool */
    protected $failOnIncomplete;

    /** @var bool */
    protected $stopOnFailure;

    /** @var int */
    protected $batchCount = 0;

    public function __construct(
        ReaderFactory $readerFactory,
        EntityManager $entityManager,
        ResourceProcessorInterface $resourceProcessor,
        ImportResultLoggerInterface $importerResult,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        $this->readerFactory = $readerFactory;
        $this->entityManager = $entityManager;
        $this->resourceProcessor = $resourceProcessor;
        $this->result = $importerResult;
        $this->batchSize = $batchSize;
        $this->failOnIncomplete = $failOnIncomplete;
        $this->stopOnFailure = $stopOnFailure;
    }

    public function import(string $fileName): ImporterResultInterface
    {
        $reader = $this->readerFactory->getReader(new \SplFileObject($fileName));

        $this->result->start();

        foreach ($reader as $i => $row) {
            if ($this->importData((int) $i, $row)) {
                break;
            }
        }

        if ($this->batchCount) {
            $this->entityManager->flush();
        }

        $this->result->stop();

        return $this->result;
    }

    public function importData(int $i, array $row): bool
    {
        try {
            $this->resourceProcessor->process($row);
            $this->result->success($i);

            ++$this->batchCount;
            if ($this->batchSize && $this->batchCount === $this->batchSize) {
                $this->entityManager->flush();
                $this->batchCount = 0;
            }
        } catch (ItemIncompleteException $e) {
            $this->result->setMessage($e->getMessage());
            $this->result->getLogger()->critical($e->getMessage());
            if ($this->failOnIncomplete) {
                $this->result->failed($i);
                if ($this->stopOnFailure) {
                    return true;
                }
            } else {
                $this->result->skipped($i);
            }
        } catch (ImporterException $e) {
            $this->result->failed($i);
            $this->result->setMessage($e->getMessage());
            $this->result->getLogger()->critical($e->getMessage());
            if ($this->stopOnFailure) {
                return true;
            }
        }

        return false;
    }
}
