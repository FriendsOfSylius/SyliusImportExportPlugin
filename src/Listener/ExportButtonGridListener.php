<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Listener;

use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Symfony\Component\HttpFoundation\RequestStack;

final class ExportButtonGridListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var array
     */
    private $links = [];

    /**
     * @param string $resource
     * @param array $formats
     */
    public function __construct(string $resource, array $formats)
    {
        $this->resource = $resource;
        $this->formats = $formats;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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

        $action = Action::fromNameAndType('export', 'links');
        $action->setLabel('fos.import_export.ui.export');
        $action->setOptions([
            'class' => '',
            'icon' => 'save',
            'header' => [
                'icon' => 'file code outline',
                'label' => 'sylius.ui.type',
            ],
            'links' => $this->createLinks(),
        ]);

        $actionGroup->addAction($action);
    }

    /**
     * @return array
     */
    private function createLinks(): array
    {
        if (empty($this->links)) {
            foreach ($this->formats as $format) {
                $this->addLink($format);
            }
        }

        return $this->links;
    }

    /**
     * @param string $format
     */
    private function addLink(string $format): void
    {
        $parameters = [
            'resource' => $this->resource,
            'format' => $format,
        ];

        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest) {
            $parameters['criteria'] = $currentRequest->query->get('criteria');
        }

        $this->links[$format] = [
            'label' => 'fos.import_export.ui.types.' . $format,
            'icon' => 'file archive',
            'route' => 'app_export_data',
            'parameters' => $parameters,
        ];
    }
}
