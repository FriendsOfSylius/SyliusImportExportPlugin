<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use ArrayAccess;
use Behat\Behat\Context\Context;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use PHPUnit\Framework\Assert;
use Sylius\Behat\Context\Transform\CountryContext;
use Sylius\Behat\Page\Admin\Crud\IndexPageInterface;
use Symfony\Component\Routing\RouterInterface;

final class CountriesContext extends SymfonyPage implements Context
{
    /** @var IndexPageInterface */
    private $countryIndexPage;

    /** @var CountryContext */
    private $countryContext;

    public function __construct(
        IndexPageInterface $countryIndexPage,
        CountryContext $countryContext,
        Session $session,
        ArrayAccess $parameters,
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

    public function getResourceName(): string
    {
        return 'sylius.country';
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
     * @Then I should see an import button
     */
    public function iShouldSeeImportButton()
    {
        Assert::assertEquals(
            'Import',
            $this->getElement('import_button')->getText()
        );
    }

    /**
     * @Then I click an import button
     */
    public function iClickImportButton()
    {
        $button = $this->getElement('import_button')->click();
    }

    /**
     * @Then I click on :element
     */
    public function iClickOn($element)
    {
        $page = $this->getSession()->getPage();
        $findName = $page->find('css', $element);
        if (!$findName) {
            throw new ElementNotFoundException($element . ' could not be found');
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
            'import_button' => sprintf(
                '.button[href="%s"]',
                $this->router->generate('fos_sylius_import_export_import_data', ['resource' => $this->getResourceName()])
            ),
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

        throw new ResponseTextException(sprintf("Response '%s' does not contain: '%s'", $responseText, $text), $this->getDriver());
    }
}
