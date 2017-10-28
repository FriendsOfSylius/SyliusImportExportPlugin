<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Sylius\Component\Taxation\Model\TaxCategoryInterface;

final class TaxCategoriesImporter extends AbstractImporter
{
    /** @var array */
    protected $headerKeys = ['Code', 'Name', 'Description'];

    protected function createOrUpdateObject(array $row): void
    {
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
