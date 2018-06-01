<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class CliOrdersContext extends CliBaseContext
{
    /**
     * @Then I should have at least the following order ids in the database:
     */
    public function iShouldHaveAtLeastTheFollowingOrdersIdsInTheDatabase(TableNode $orderIds)
    {
        foreach ($orderIds as $orderId) {
            $order = $this->repository->findBy(['number' => $orderId]);
            Assert::assertNotNull($order);
        }
    }

    /**
     * @Then I should have at least the following order ids in the file :fileName:
     */
    public function iShouldHaveAtLeastTheFollowingCountryIdsInTheFile($fileName, TableNode $table)
    {
        throw new PendingException();
    }
}
