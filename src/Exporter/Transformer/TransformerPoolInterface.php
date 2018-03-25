<?php

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer;

interface TransformerPoolInterface
{
    /**
     * @param mixed $key
     * @param mixed $value
     * @return mixed (Something that can cast to string at least)
     */
    public function handle($key, $value);
}
