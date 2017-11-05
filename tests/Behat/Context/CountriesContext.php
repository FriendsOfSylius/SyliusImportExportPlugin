<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Sylius\Behat\Context\Transform\CountryContext;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\ResourceIndexPageInterface;

class CountriesContext implements Context
{
    /** @var ResourceIndexPageInterface */
    private $countryIndexPage;

    /** @var CountryContext */
    private $countryContext;

    public function __construct(ResourceIndexPageInterface $countryIndexPage, CountryContext $countryContext)
    {
        $this->countryIndexPage = $countryIndexPage;
        $this->countryContext = $countryContext;
    }

    /**
     * @When I import country data from :file :format file
     */
    public function iImportCountryDataFromCsvFile(string $file, string $format)
    {
        $this->countryIndexPage->open();
        $this->countryIndexPage->importData($file, $format);
    }

    /**
     * @Then I should see :amount countries in the list
     */
    public function iShouldSeeCountriesInTheList($amount)
    {
        $itemCount = $this->countryIndexPage->countItems();
        Assert::assertEquals(
            $amount,
            $itemCount,
            'expected value differs from actual value ' . $amount . ' !== ' . $itemCount
        );
    }

    /**
     * @Then the country :country should appear in the registry
     */
    public function theCountryShouldAppearInTheRegistry($countryname)
    {
        $country = $this->countryContext->getCountryByName($countryname);

        Assert::assertNotNull(
            $country,
            sprintf('Country with name "%s" does not exist', $countryname)
        );
    }
}
