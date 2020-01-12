<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Admin\Crud\IndexPageInterface;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\ResourceIndexPageInterface;
use Webmozart\Assert\Assert;

class TaxCategoriesContext implements Context
{
    /** @var ResourceIndexPageInterface */
    private $taxCategoryIndexPage;

    public function __construct(IndexPageInterface $taxCategoryIndexPage)
    {
        $this->taxCategoryIndexPage = $taxCategoryIndexPage;
    }

    /**
     * @When I browse all tax categories
     */
    public function iBrowseTaxCategories(): void
    {
        $this->taxCategoryIndexPage->open();
    }

    /**
     * @Then I should see :count tax categories in the list
     */
    public function iShouldSeeTaxCategoriesInTheList(int $count): void
    {
        Assert::eq($count, $this->taxCategoryIndexPage->countItems());
    }
}
