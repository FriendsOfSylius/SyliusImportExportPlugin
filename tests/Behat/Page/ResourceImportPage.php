<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Page;

use ArrayAccess;
use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Symfony\Component\Routing\RouterInterface;

class ResourceImportPage extends SymfonyPage implements ResourceImportPageInterface
{
    /** @var string */
    protected $filesPath;

    public function __construct(
        Session $session,
        ArrayAccess $parameters,
        RouterInterface $router,
        string $filesPath
    ) {
        parent::__construct($session, $parameters, $router);
        $this->filesPath = $filesPath;
    }

    public function getRouteName(): string
    {
        return 'fos_sylius_import_export_import_data';
    }

    public function importData(string $filePath, string $format): void
    {
        $this
            ->getDocument()
            ->find('css', 'input[id="import_file"]')
            ->attachFile($this->filesPath . '/' . $filePath)
        ;

        $this
            ->getDocument()
            ->find('css', 'select[id="import_format"]')
            ->selectOption($format)
        ;

        $this->getDocument()->pressButton('import-data');
    }
}
