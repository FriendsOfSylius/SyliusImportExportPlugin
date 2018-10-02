<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\TaxonCreatePageInterface;
use Webmozart\Assert\Assert;

class TaxonomiesContext implements Context
{
    /** @var TaxonCreatePageInterface */
    private $taxonomyCreatePage;

    public function __construct(TaxonCreatePageInterface $taxonomyCreatePage)
    {
        $this->taxonomyCreatePage = $taxonomyCreatePage;
    }

    /**
     * @When I import taxonomy data from :file :format file
     */
    public function iImportTaxonomyDataFromFormatFile(string $file, string $format): void
    {
        $this->taxonomyCreatePage->importData($file, $format);
    }

    /**
     * @When I browse taxonomies tree
     */
    public function iBrowseTaxonomiesTree(): void
    {
        $this->taxonomyCreatePage->open();
    }

    /**
     * @Then I should see :count taxonomies in the tree
     */
    public function iShouldSeeTaxonomiesInTheTree(int $count): void
    {
        Assert::eq($count, $this->taxonomyCreatePage->countTaxons());
    }

    /**
     * @Then the taxonomy :taxonName should appear in the tree
     */
    public function theTaxonomyCodeShouldAppearInTheTree(string $taxonName): void
    {
        Assert::true($this->taxonomyCreatePage->hasTaxonWithName($taxonName));
    }
}
