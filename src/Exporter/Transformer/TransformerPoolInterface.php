<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer;

interface TransformerPoolInterface
{
    /**
     * @return mixed (Something that can cast to string at least)
     */
    public function handle($key, $value);
}
