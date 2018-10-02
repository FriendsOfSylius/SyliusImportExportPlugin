<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

final class CliTaxonomiesContext extends CliBaseContext
{
    /**
     * @Then I should have at least the following taxonomy codes in the database:
     */
    public function iShouldHaveAtLeastTheFollowingTaxonomyCodesInTheDatabase(TableNode $taxonomyCodes)
    {
        foreach ($taxonomyCodes as $taxonomyCode) {
            $taxonomy = $this->repository->findBy(['code' => $taxonomyCode]);
            Assert::assertNotNull($taxonomy);
        }
    }
}
