<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

final class DateTimeToStringHandler extends Handler
{
    /**
     * @var string
     */
    private $format;

    /**
     * @param string $format
     */
    public function __construct(string $format = 'Y-m-d')
    {
        $this->format = $format;
    }

    /**
     * @param mixed $key
     * @param \DateTimeInterface $value
     *
     * @return string|null
     */
    protected function process($key, $value)
    {
        return $value->format($this->format);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return bool
     */
    protected function allows($key, $value): bool
    {
        return $value instanceof \DateTimeInterface;
    }
}
