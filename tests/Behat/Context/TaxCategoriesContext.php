<?php

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\TaxCategoryIndexPageInterface;
use Webmozart\Assert\Assert;

class TaxCategoriesContext implements Context
{
    /** @var TaxCategoryIndexPageInterface */
    private $taxCategoryIndexPage;

    public function __construct(TaxCategoryIndexPageInterface $taxCategoryIndexPage)
    {
        $this->taxCategoryIndexPage = $taxCategoryIndexPage;
    }

    /**
     * @When I import tax category data from :file file
     */
    public function iImportTaxClassDataFromFile(string $file) :void
    {
        $this->taxCategoryIndexPage->importData($file);
    }

    /**
     * @When I browse tax categories
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
