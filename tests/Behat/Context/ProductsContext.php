<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use PHPUnit\Framework\Assert;
use Sylius\Behat\Context\Transform\ProductContext;
use Symfony\Component\Routing\RouterInterface;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\ResourceIndexPageInterface;

final class ProductsContext extends SymfonyPage implements Context
{
    /** @var ResourceIndexPageInterface */
    private $productIndexPage;
    /** @var ProductContext */
    private $productContext;

    public function __construct(
        ResourceIndexPageInterface $productIndexPage,
        ProductContext $productContext,
        Session $session,
        array $parameters,
        RouterInterface $router
    ) {
        $this->productIndexPage = $productIndexPage;
        $this->productContext = $productContext;

        parent::__construct($session, $parameters, $router);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName(): string
    {
        return 'sylius_admin_product_index';
    }

    /**
     * @When I import product data from :file :format file
     */
    public function iImportProductDataFromCsvFile(string $file, string $format): void
    {
        $this->productIndexPage->importData($file, $format);
    }

    /**
     * @When I open the product admin index page
     */
    public function iOpenTheProductIndexPage(): void
    {
        $this->productIndexPage->open();
    }

    /**
     * @Then I should see an export button
     */
    public function iShouldSeeExportButton(): void
    {
        Assert::assertEquals(
            'Export',
            $this->getElement('export_button_text')->getText()
        );
    }

    /**
     * @Then I click on :element
     */
    public function iClickOn(string $element): void
    {
        $page = $this->getSession()->getPage();
        $findName = $page->find('css', $element);
        if (!$findName) {
            throw new ElementNotFoundException($element . ' could not be found');
        }
        $findName->click();
    }

    /**
     * @Then I should see a link to export products to CSV
     */
    public function iShouldSeeExportCSVLink(): void
    {
        Assert::assertContains(
            'CSV',
            $this->getElement('export_links')->find('css', 'a.item')->getText()
        );
    }

    /**
     * @Then the product :product should appear in the registry
     */
    public function theProductShouldAppearInTheRegistry(string $productname): void
    {
        $product = $this->productContext->getProductByName($productname);

        Assert::assertNotNull(
            $product,
            sprintf('Product with name "%s" does not exist', $productname)
        );
    }

    /**
     * @Then :amount products should be in the registry
     */
    public function amountProductsShouldBeInTheRegistry(int $amount): void
    {
        $productCount = $this->productContext->getProductCount();

        Assert::assertEquals(
            $amount,
            $productCount,
            'expected value differs from actual value ' . $amount . ' !== ' . $productCount
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
    public function goToSpecificHomepage(string $hp): void
    {
        $this->getSession(null)->visit($hp);
    }

    /**
     * Checks that response body contains specific text.
     *
     * @Then response should contain :text
     */
    public function theResponseShouldContain(string $text): void
    {
        $responseText = $this->getSession()->getPage()->getContent();

        if (strpos($responseText, $text) !== false) {
            return;
        }

        throw new ResponseTextException(sprintf("Response '%s' does not contain: '%s'", $responseText, $text), $this->getDriver());
    }
}
