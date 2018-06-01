<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

use Sylius\Component\Core\Model\AddressInterface;

interface AddressConcatenationInterface
{
    /**
     * @param AddressInterface $address
     *
     * @return string
     */
    public function getString(AddressInterface $address): string;
}
