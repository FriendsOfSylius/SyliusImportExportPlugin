<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer;

interface ImporterInterface
{
    public function import(string $fileName): ImporterResultInterface;
}
