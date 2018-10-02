<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page;

use Sylius\Behat\Page\Admin\Taxon\CreatePageInterface as SyliusTaxonCreatePageInterface;

interface TaxonCreatePageInterface extends SyliusTaxonCreatePageInterface
{
    public function importData(string $filePath, string $format): void;
}
