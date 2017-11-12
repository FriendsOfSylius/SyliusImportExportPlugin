<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use Sylius\Component\Addressing\Model\CountryInterface;

class CountriesImporter extends AbstractImporter
{
    /** @var array */
    protected $headerKeys = ['Code'];

    /**
     * @param array $row
     */
    protected function createOrUpdateObject(ImporterResult $result, array $row): void
    {
        if (empty($row['Code'])) {
            throw new ItemIncompleteException();
        }

        $country = $this->repository->findOneBy(['code' => $row['Code']]);

        if ($country === null) {
            /** @var CountryInterface $country */
            $country = $this->factory->createNew();
            $country->setCode($row['Code']);

            $this->objectManager->persist($country);
        }
    }
}
