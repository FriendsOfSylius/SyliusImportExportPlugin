<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

use Sylius\Component\Core\Model\AddressInterface;

interface AddressConcatenationInterface
{
    public function getString(AddressInterface $address): string;
}
