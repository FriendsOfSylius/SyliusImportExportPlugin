<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use Port\Reader;
use Port\Reader\ReaderFactory;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

abstract class AbstractImporter implements ImporterInterface
{
    /** @var ReaderFactory */
    protected $readerFactory;

    /** @var FactoryInterface */
    protected $factory;

    /** @var RepositoryInterface */
    protected $repository;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var array */
    protected $headerKeys;

    /**
     * @param ReaderFactory $readerFactory
     * @param FactoryInterface $factory
     * @param RepositoryInterface $repository
     * @param ObjectManager $objectManager
     *
     * @throws ImporterException
     */
    public function __construct(
        ReaderFactory $readerFactory,
        FactoryInterface $factory,
        RepositoryInterface $repository,
        ObjectManager $objectManager
    ) {
        $this->readerFactory = $readerFactory;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->objectManager = $objectManager;

        if (empty($this->headerKeys)) {
            throw new ImporterException('The "headerKeys" property is not set on the concrete class');
        }
    }

    /**
     * @param string $fileName
     *
     * @return ImporterResult
     *
     * @throws ImporterException
     */
    public function import(string $fileName): ImporterResult
    {
        $reader = $this->readerFactory->getReader(new \SplFileObject($fileName));

        $this->assertKeys($reader);

        $result = new ImporterResult();
        $result->start();
        foreach ($reader as $i => $row) {
            try {
                $this->createOrUpdateObject($result, $row);
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
    protected function assertKeys(Reader $reader)
    {
        if (!method_exists($reader, 'getColumnHeaders')) {
            throw new ImporterException('Missing "getColumnHeaders" method on reader');
        }

        $missingHeaders = array_diff($this->headerKeys, $reader->getColumnHeaders());
        if (!empty($missingHeaders)) {
            throw new ImporterException('Missing expected headers: ' . implode(', ', $missingHeaders));
        }
    }

    /**
     * @param ImporterResult $result
     * @param array $row
     *
     * @throws \Exception
     */
    protected function createOrUpdateObject(ImporterResult $result, array $row): void
    {
        throw new \Exception(
            sprintf(
                'Method %s has to be implemented in the importer %s',
                __FUNCTION__,
                static::class)
        );
    }
}
