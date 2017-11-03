<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page;

use Sylius\Behat\Page\Admin\Crud\IndexPageInterface;

interface ResourceIndexPageInterface extends IndexPageInterface
{
    public function importData(string $filePath): void;
}
