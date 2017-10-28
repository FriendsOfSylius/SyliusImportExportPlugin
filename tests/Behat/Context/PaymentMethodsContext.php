<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\ResourceIndexPageInterface;

final class PaymentMethodsContext implements Context
{
    /** @var ResourceIndexPageInterface */
    private $paymentMethodIndexPage;

    public function __construct(
        ResourceIndexPageInterface $paymentMethodIndexPage
    ) {
        $this->paymentMethodIndexPage = $paymentMethodIndexPage;
    }

    /**
     * @Given I import payment methods data from :file file
     */
    public function iImportPaymentMethodsDataFromFile(string $file): void
    {
        $this->paymentMethodIndexPage->importData($file);
    }
}
