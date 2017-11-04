<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Sylius\Component\Addressing\Model\CountryInterface;

class CountriesImporter extends AbstractImporter
{
    /** @var array */
    protected $headerKeys = ['Code'];

    /**
     * @param array $row
     */
    protected function createOrUpdateObject(array $row): void
    {
        $country = $this->repository->findOneBy(['code' => $row['Code']]);

        if ($country === null) {
            /** @var CountryInterface $country */
            $country = $this->factory->createNew();
            $country->setCode($row['Code']);

            $this->objectManager->persist($country);
        }
    }
}
