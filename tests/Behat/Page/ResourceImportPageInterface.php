<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page;

use FriendsOfBehat\PageObjectExtension\Page\PageInterface;

interface ResourceImportPageInterface extends PageInterface
{
    public function importData(string $filePath, string $format): void;
}
