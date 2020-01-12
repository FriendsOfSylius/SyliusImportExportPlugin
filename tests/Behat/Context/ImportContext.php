<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\ResourceImportPageInterface;
use Webmozart\Assert\Assert;

class ImportContext implements Context
{
    /** @var ResourceImportPageInterface */
    private $importPage;

    /** @var array */
    private $resourceAliases = [
        'country' => 'sylius.country',
        'customer group' => 'sylius.customer_group',
        'payment method' => 'sylius.payment_method',
        'product' => 'sylius.product',
        'tax category' => 'sylius.tax_category',
    ];

    public function __construct(ResourceImportPageInterface $importPage)
    {
        $this->importPage = $importPage;
    }

    /**
     * @Given /^I am on (.*) import page$/i
     */
    public function iAmOnImportPage(string $resource): void
    {
        $this->importPage->open(['resource' => $this->getResourceName($resource)]);
    }

    /**
     * @When I import data from :file :format file
     */
    public function iImportDataFromCsvFile(string $file, string $format): void
    {
        $this->importPage->importData($file, $format);
    }

    /**
     * @Then /^I should be on (.*) import page$/i
     */
    public function iShouldBeOnImportPage(string $resource): void
    {
        Assert::true($this->importPage->isOpen(['resource' => $this->getResourceName($resource)]));
    }

    private function getResourceName(string $alias): string
    {
        if (!array_key_exists($alias, $this->resourceAliases)) {
            throw new \InvalidArgumentException(sprintf('"%s" resource alias is not defined.', $alias));
        }

        return $this->resourceAliases[$alias];
    }
}
