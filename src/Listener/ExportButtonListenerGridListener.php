<?php

namespace FriendsOfSylius\SyliusImportExportPlugin\Listener;

use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;

final class ExportButtonListenerGridListener
{
    /**
     * @var string
     */
    private $resource;

    /**
     * @var string
     */
    private $format;

    /**
     * ExportButtonListenerGridListener constructor.
     * @param string $resource
     * @param string $format
     */
    public function __construct(string $resource, string $format)
    {
        $this->resource = $resource;
        $this->format = $format;
    }

    /**
     * @param GridDefinitionConverterEvent $event
     */
    public function onSyliusGridAdmin(GridDefinitionConverterEvent $event)
    {
        $grid = $event->getGrid();

        if (!$grid->hasActionGroup('main')) {
            $grid->addActionGroup(ActionGroup::named('main'));
        }

        $actionGroup = $grid->getActionGroup('main');

        if ($actionGroup->hasAction('export')) {
            return;
        }

        $action = Action::fromNameAndType('name', 'links');
        $action->setLabel('fos.import_export.ui.export');
        $action->setOptions([
            'class' => '',
            'icon' => 'save',
            'header' => [
                'icon' => 'file code outline',
                'label' => 'sylius.ui.type',
            ],
            'links' => [
                $this->format => [
                    'label' => 'fos.import_export.ui.types.' . $this->format,
                    'icon' => 'file archive',
                    'route' => 'app_export_data',
                    'parameters' => [
                        'resource' => $this->resource,
                        'format' => $this->format,
                    ]
                ]
            ],
        ]);

        $actionGroup->addAction($action);
    }
}
