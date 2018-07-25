<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

use Sylius\Component\Core\Model\AddressInterface;

final class DefaultAddressConcatenation implements AddressConcatenationInterface
{
    public function getString(AddressInterface $address): string
    {
        return sprintf(
            '%s, %s, %s, %s, %s',
            $address->getFullName(),
            $address->getStreet(),
            $address->getCity(),
            $address->getPostcode(),
            $address->getCountryCode()
        );
    }
}
