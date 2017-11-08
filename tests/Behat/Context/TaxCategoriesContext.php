<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\ResourceIndexPageInterface;
use Webmozart\Assert\Assert;

class TaxCategoriesContext implements Context
{
    /** @var ResourceIndexPageInterface */
    private $taxCategoryIndexPage;

    public function __construct(ResourceIndexPageInterface $taxCategoryIndexPage)
    {
        $this->taxCategoryIndexPage = $taxCategoryIndexPage;
    }

    /**
     * @When I import tax category data from :file :format file
     */
    public function iImportTaxClassDataFromCsvFile(string $file, string $format): void
    {
        $this->taxCategoryIndexPage->importData($file, $format);
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
