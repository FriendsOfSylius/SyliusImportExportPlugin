<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

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
    protected function process($key, $value)
    {
        return $value->format($this->format);
    }

    protected function allows($key, $value): bool
    {
        return $value instanceof \DateTimeInterface;
    }
}
