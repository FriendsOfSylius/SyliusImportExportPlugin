<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;
use PhpOffice\PhpSpreadsheet\Shared\Date;

final class DateTimeToStringHandler extends Handler
{
    /** @var string */
    private $format;

    public function __construct(string $format = 'Y-m-d')
    {
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function process(?string $key, $value): string
    {
        return $value->format($this->format);
    }

    /**
     * {@inheritdoc}
     */
    protected function allows(?string $key, $value): bool
    {
        return $value instanceof \DateTimeInterface;
    }
}
