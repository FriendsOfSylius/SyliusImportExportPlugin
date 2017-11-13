<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

final class TaxCategoriesImporter extends AbstractImporter
{
    /** @var array */
    protected $headerKeys = ['Code', 'Name', 'Description'];

    /**
     * {@inheritdoc}
     */
    protected function createOrUpdateObject(ImporterResult $result, array $row): void
    {
        if (empty($row['Code']) || empty($row['Name'])) {
            throw new ItemIncompleteException();
        }

        /** @var TaxCategoryInterface $taxCategory */
        $taxCategory = $this->repository->findOneBy(['code' => $row['Code']]);

        if (null === $taxCategory) {
            $taxCategory = $this->factory->createNew();
            $taxCategory->setCode($row['Code']);
            $this->objectManager->persist($taxCategory);
        }

        $taxCategory->setName($row['Name']);
        $taxCategory->setDescription($row['Description']);
    }
}
