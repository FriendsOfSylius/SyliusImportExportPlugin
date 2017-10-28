<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;

final class NotificationContext implements Context
{
    /** @var NotificationCheckerInterface  */
    private $notificationChecker;

    public function __construct(NotificationCheckerInterface $notificationChecker)
    {
        $this->notificationChecker = $notificationChecker;
    }

    /**
     * @Then I should see a notification that the import was successful
     */
    public function iShouldSeeANotificationThatTheImportWasSuccessful(): void
    {
        $this->notificationChecker->checkNotification(
            "Data successfully imported",
            NotificationType::success()
        );
    }
}
