<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

final class IntegerToMoneyFormatHandler extends Handler
{
    /** @var array */
    private $keys;

    private $format;

    /**
     * @param string[] $allowedKeys
     * @param string $format
     */
    public function __construct(array $allowedKeys, string $format = '%0.2f')
    {
        $this->keys = $allowedKeys;
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function process($key, $value): ?string
    {
        return sprintf($this->format, $value/100);
    }

    protected function allows($key, $value): bool
    {
        return is_int($value) && in_array($key, $this->keys);
    }
}
