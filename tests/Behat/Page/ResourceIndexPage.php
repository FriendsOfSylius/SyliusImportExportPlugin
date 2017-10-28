<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page;

use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\IndexPage;
use Sylius\Behat\Service\Accessor\TableAccessorInterface;
use Symfony\Component\Routing\RouterInterface;

class ResourceIndexPage extends IndexPage implements ResourceIndexPageInterface
{
    /** @var  string */
    protected $filesPath;

    public function __construct(
        Session $session,
        array $parameters,
        RouterInterface $router,
        TableAccessorInterface $tableAccessor,
        string $routeName,
        string $filesPath
    ) {
        parent::__construct($session, $parameters, $router, $tableAccessor, $routeName);
        $this->filesPath = $filesPath;
    }

    public function importData(string $filePath): void
    {
        $this
            ->getDocument()
            ->find('css', 'input[type="file"]')
            ->attachFile($this->filesPath.'/'.$filePath)
        ;

        $this->getDocument()->pressButton('Import data');
    }
}
