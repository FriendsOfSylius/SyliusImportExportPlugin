<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use pcrov\JsonReader\JsonReader;

class JsonResourceImporter extends ResourceImporter
{
    /** @var JsonReader */
    private $reader;

    public function __construct(
        JsonReader $reader,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult,
        int $batchSize,
        bool $failOnIncomplete,
        bool $stopOnFailure
    ) {
        $this->reader = $reader;
        $this->objectManager = $objectManager;
        $this->resourceProcessor = $resourceProcessor;
        $this->result = $importerResult;
        $this->batchSize = $batchSize;
        $this->failOnIncomplete = $failOnIncomplete;
        $this->stopOnFailure = $stopOnFailure;
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function import(string $fileName)
//    {
//        $this->result->start();
//
//        $batchCount = 0;
//        foreach ($reader as $i => $row) {
//            try {
//                $this->resourceProcessor->process($row);
//                $this->result->success($i);
//
//                ++$batchCount;
//                if ($this->batchSize && $batchCount === $this->batchSize) {
//                    $this->objectManager->flush();
//                    $batchCount = 0;
//                }
//            } catch (ItemIncompleteException $e) {
//                if ($this->failOnIncomplete) {
//                    $this->result->failed($i);
//                    if ($this->stopOnFailure) {
//                        break;
//                    }
//                } else {
//                    $this->result->skipped($i);
//                }
//            } catch (ImporterException $e) {
//                $this->result->failed($i);
//                if ($this->stopOnFailure) {
//                    break;
//                }
//            }
//        }
//
//        if ($batchCount) {
//            $this->objectManager->flush();
//        }
//
//        $this->result->stop();
//
//        return $this->result;
//    }
}
