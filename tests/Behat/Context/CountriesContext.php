<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use PHPUnit\Framework\Assert;
use Sylius\Behat\Context\Transform\CountryContext;
use Symfony\Component\Routing\RouterInterface;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\ResourceIndexPageInterface;

final class CountriesContext extends SymfonyPage implements Context
{
    /** @var ResourceIndexPageInterface */
    private $countryIndexPage;

    /** @var CountryContext */
    private $countryContext;

    public function __construct(
        ResourceIndexPageInterface $countryIndexPage,
        CountryContext $countryContext,
        Session $session,
        array $parameters,
        RouterInterface $router
    ) {
        $this->countryIndexPage = $countryIndexPage;
        $this->countryContext = $countryContext;

        parent::__construct($session, $parameters, $router);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName(): string
    {
        return 'sylius_admin_country_index';
    }

    /**
     * @When I import country data from :file :format file
     */
    public function iImportCountryDataFromCsvFile(string $file, string $format)
    {
        $this->countryIndexPage->importData($file, $format);
    }

    /**
     * @When I open the country admin index page
     */
    public function iOpenTheCountryIndexPage()
    {
        $this->countryIndexPage->open();
    }

    /**
     * @When I open the country admin index second page
     */
    public function iOpenTheCountryIndexSecondPage()
    {
        $this->countryIndexPage->open(['page' => 2]);
    }

    /**
     * @Then I should see an export button
     */
    public function iShouldSeeExportButton()
    {
        Assert::assertEquals(
            'Export',
            $this->getElement('export_button_text')->getText()
        );
    }

    /**
     * @Then I click on :element
     */
    public function iClickOn($element)
    {
        $page = $this->getSession()->getPage();
        $findName = $page->find('css', $element);
        if (!$findName) {
            throw new Exception($element . ' could not be found');
        }
        $findName->click();
    }

    /**
     * @Then I should see a link to export countries to CSV
     */
    public function iShouldSeeExportCSVLink()
    {
        Assert::assertContains(
            'CSV',
            $this->getElement('export_links')->find('css', 'a.item')->getText()
        );
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

    /**
     * @Then :amount countries should be in the registry
     */
    public function amountCountriesShouldBeInTheRegistry($amount)
    {
        $countryCount = $this->countryContext->getCountryCount();

        Assert::assertEquals(
            $amount,
            $countryCount,
            'expected value differs from actual value ' . $amount . ' !== ' . $countryCount
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'export_button_text' => '.buttons div.dropdown span.text',
            'export_links' => '.buttons div.dropdown div.menu',
        ]);
    }

    /**
     * @When I go to :hp homepage
     */
    public function goToSpecificHomepage($hp)
    {
        $this->getSession(null)->visit($hp);
    }

    /**
     * Checks that response body contains specific text.
     *
     * @param string $text
     *
     * @Then response should contain :text
     */
    public function theResponseShouldContain($text)
    {
        $responseText = $this->getSession()->getPage()->getContent();

        if (strpos($responseText, $text) !== false) {
            return;
        }

        throw new ResponseTextException('Response does not contain: ' . $text);
    }
}
