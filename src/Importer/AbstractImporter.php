<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use Port\Csv\CsvReaderFactory;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

abstract class AbstractImporter implements ImporterInterface
{
    /** @var CsvReaderFactory */
    protected $csvReaderFactory;

    /** @var FactoryInterface */
    protected $factory;

    /** @var RepositoryInterface */
    protected $repository;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var array */
    protected $headerKeys;

    public function __construct(
        CsvReaderFactory $csvReaderFactory,
        FactoryInterface $factory,
        RepositoryInterface $repository,
        ObjectManager $objectManager
    ) {
        $this->csvReaderFactory = $csvReaderFactory;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->objectManager = $objectManager;

        if (empty($this->headerKeys)) {
            throw new ImporterException('The "headerKeys" property is not set on the concrete class');
        }
    }

    public function import(string $fileName): void
    {
        $csvReader = $this->csvReaderFactory->getReader(new \SplFileObject($fileName));

        $missingHeaders = array_diff($this->headerKeys, $csvReader->getColumnHeaders());
        if (!empty($missingHeaders)) {
            throw new ImporterException('Missing expected headers: ' . implode(', ', $missingHeaders));
        }

        foreach ($csvReader as $row) {
            $this->createOrUpdateObject($row);
        }

        $this->objectManager->flush();
    }

    protected function createOrUpdateObject(array $row)
    {
        throw new \Exception(
            sprintf(
                'Method %s has to be implemented in the importer %s',
                __FUNCTION__,
                static::class)
        );
    }
}
