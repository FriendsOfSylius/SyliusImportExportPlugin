<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page\ResourceIndexPageInterface;

class CustomerGroupsContext implements Context
{
    /** @var ResourceIndexPageInterface */
    private $customerGroupIndexPage;

    public function __construct(
        ResourceIndexPageInterface $customerGroupIndexPage
    ) {
        $this->customerGroupIndexPage = $customerGroupIndexPage;
    }

    /**
     * @When I import customer-group data from :filename :format file
     */
    public function iImportCustomerGroupDataFromCsvFile($filename, $format)
    {
        $this->customerGroupIndexPage->open();
        $this->customerGroupIndexPage->importData($filename, $format);
    }
}
