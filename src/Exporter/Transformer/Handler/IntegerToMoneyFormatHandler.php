<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;
use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatter;
use Sylius\Component\Locale\Context\LocaleContextInterface;

final class IntegerToMoneyFormatHandler extends Handler
{
    /** @var array */
    private $keys;

    /** @var LocaleContextInterface */
    private $localeContext;

    /**
     * @param string[] $allowedKeys
     * @param LocaleContextInterface $localeContext
     */
    public function __construct(array $allowedKeys, $localeContext)
    {
        $this->keys = $allowedKeys;
        $this->localeContext = $localeContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function process($key, $value): ?string
    {
        return number_format($value, 2, '.', '');
        return (new MoneyFormatter())->format($value, "EUR", $this->localeContext->getLocaleCode());
    }

    protected function allows($key, $value): bool
    {
        return is_int($value) && in_array($key, $this->keys);
    }
}
