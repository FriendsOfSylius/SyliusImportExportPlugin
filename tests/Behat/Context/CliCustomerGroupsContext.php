<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class CliCustomerGroupsContext extends CliBaseContext
{
    /**
     * @Then I should have at least the following customer-group ids in the database:
     */
    public function iShouldHaveAtLeastTheFollowingCustomerGroupIdsInTheDatabase(TableNode $customerGroupIds)
    {
        foreach ($customerGroupIds as $customerGroupId) {
            $customerGroup = $this->repository->findBy(['code' => $customerGroupId]);
            Assert::assertNotNull($customerGroup);
        }
    }
}
