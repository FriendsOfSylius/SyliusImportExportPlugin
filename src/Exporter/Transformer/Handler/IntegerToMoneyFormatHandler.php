<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

final class IntegerToMoneyFormatHandler extends Handler
{
    /**
     * @var array
     */
    private $keys;

    /**
     * @var string
     */
    private $format;

    /**
     * @param array $allowedKeys
     * @param string $format
     */
    public function __construct(array $allowedKeys, string $format = '%.2n')
    {
        $this->keys = $allowedKeys;
        $this->format = $format;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function process($key, $value)
    {
        return money_format($this->format, $value / 100);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return bool
     */
    protected function allows($key, $value): bool
    {
        return is_int($value) && in_array($key, $this->keys);
    }
}
