<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer;

interface TransformerPoolInterface
{
    /**
     * @return mixed (Something that can cast to string at least)
     * @param int|\DateTime|array $value
     */
    public function handle(?string $key, $value);
}
