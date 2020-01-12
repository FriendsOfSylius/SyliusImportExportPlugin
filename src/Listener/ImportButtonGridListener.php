<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Listener;

use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;

final class ImportButtonGridListener
{
    /** @var string */
    private $resource;

    public function __construct(string $resource)
    {
        $this->resource = $resource;
    }

    public function onSyliusGridAdmin(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();

        if (!$grid->hasActionGroup('main')) {
            $grid->addActionGroup(ActionGroup::named('main'));
        }

        $actionGroup = $grid->getActionGroup('main');

        if ($actionGroup->hasAction('import')) {
            return;
        }

        $action = Action::fromNameAndType('import', 'default');
        $action->setLabel('fos.import_export.ui.import');
        $action->setIcon('upload');
        $action->setOptions([
            'link' => [
                'route' => 'fos_sylius_import_export_import_data',
                'parameters' => ['resource' => $this->resource],
            ],
        ]);

        $actionGroup->addAction($action);
    }
}
