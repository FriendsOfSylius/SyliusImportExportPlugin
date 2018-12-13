<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

final class CliTaxCategoriesContext extends CliBaseContext
{
    /**
     * @Then I should have at least the following tax categories ids in the database:
     */
    public function iShouldHaveAtLeastTheFollowingTaxCategoriesIdsInTheDatabase(TableNode $taxCategoryIds)
    {
        foreach ($taxCategoryIds as $taxCategoryId) {
            $taxCategory = $this->repository->findBy(['code' => $taxCategoryId]);
            Assert::assertNotNull($taxCategory);
        }
    }

    /**
     * @Then /^(this tax category) name should be "([^"]+)"$/
     */
    public function thisTaxCategoryNameShouldBe(TaxCategoryInterface $taxCategory, $taxCategoryName)
    {
        $taxCategory->setName($taxCategoryName);
    }

    /**
     * @Then /^(this tax category) description should be "([^"]+)"$/
     */
    public function thisTaxCategoryDescriptionShouldBe(TaxCategoryInterface $taxCategory, $taxCategoryName)
    {
        $taxCategory->setDescription($taxCategoryName);
    }

}
