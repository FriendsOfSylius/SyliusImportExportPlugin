<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class CliCountriesContext extends CliBaseContext
{
    /**
     * @Then I should have at least the following country ids in the database:
     */
    public function iShouldHaveAtLeastTheFollowingTaxCategoriesIdsInTheDatabase(TableNode $paymentMethodIds)
    {
        foreach ($paymentMethodIds as $paymentMethodId) {
            $paymentMethod = $this->repository->findBy(['code' => $paymentMethodId]);
            Assert::assertNotNull($paymentMethod);
        }
    }
}
