<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

final class CliProductsContext extends CliBaseContext
{
    /**
     * @Then I should have at least the following product ids in the database:
     */
    public function iShouldHaveAtLeastTheFollowingTaxCategoriesIdsInTheDatabase(TableNode $productIds): void
    {
        foreach ($productIds as $productId) {
            $product = $this->repository->findBy(['code' => $productId]);
            Assert::assertNotNull($product);
        }
    }
}
