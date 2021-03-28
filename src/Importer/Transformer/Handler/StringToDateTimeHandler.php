<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\Handler;

final class StringToDateTimeHandler extends Handler
{
    /** @var string */
    private $format;

    public function __construct(string $format = 'Y-m-d')
    {
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     * @return \DateTime|bool
     */
    protected function process(?string $type, string $value)
    {
        return \DateTime::createFromFormat($this->format, $value);
    }

    protected function allows(?string $type, string $value): bool
    {
        return $type === 'datetime';
    }
}
