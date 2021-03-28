<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer;

interface TransformerPoolInterface
{
    /**
     * @return mixed (Something that can cast from string)
     */
    public function handle(?string $type, string $value);
}
