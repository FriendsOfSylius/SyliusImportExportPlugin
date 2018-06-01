<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

interface StringArrayConcatenationInterface
{
    /**
     * @param array $messageArray
     *
     * @return string
     */
    public function getConcatenatedString(array $messageArray): string;
}
