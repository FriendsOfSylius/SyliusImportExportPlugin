<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
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
     * @throws ImporterException
     */
    public function import(string $fileName): void
    {
        $reader = $this->readerFactory->getReader(new \SplFileObject($fileName));

        $this->assertKeys($reader);

        foreach ($reader as $row) {
            $this->createOrUpdateObject($row);
        }

        $this->objectManager->flush();
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
     * @param array $row
     *
     * @throws \Exception
     */
    protected function createOrUpdateObject(array $row): void
    {
        throw new \Exception(
            sprintf(
                'Method %s has to be implemented in the importer %s',
                __FUNCTION__,
                static::class)
        );
    }
}
