<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Writer;

interface WriterInterface
{
    /**
     * @param array $data
     */
    public function write(array $data): void;

    /**
     * @param string $filename
     */
    public function setFile(string $filename): void;

    /**
     * @param string $filename
     * @return string
     */
    public function getFileContent(string $filename): string;
}
