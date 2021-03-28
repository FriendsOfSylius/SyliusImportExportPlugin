<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Listener;

use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Symfony\Component\HttpFoundation\RequestStack;

final class ExportButtonGridListener
{
    /** @var RequestStack */
    private $requestStack;

    /** @var string */
    private $resource;

    /** @var string[] */
    private $formats;

    /**
     * @param string[] $formats
     */
    public function __construct(string $resource, array $formats)
    {
        $this->resource = $resource;
        $this->formats = $formats;
    }

    public function setRequest(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function onSyliusGridAdmin(GridDefinitionConverterEvent $event): void
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
     * @return array[]
     */
    private function createLinks(): array
    {
        $links = [];
        foreach ($this->formats as $format) {
            $links[] = $this->addLink($format);
        }

        return $links;
    }

    private function addLink(string $format): array
    {
        $parameters = [
            'resource' => $this->resource,
            'format' => $format,
        ];

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null !== $currentRequest) {
            // @TODO Find way to validate the list of criteria injected
            $parameters['criteria'] = $currentRequest->query->get('criteria');
        }

        $resource = $this->resource;

        $explode = explode('.', $this->resource);
        if (false != $explode && [] != $explode) {
            $resource = \end($explode);
        }

        return [
            'label' => 'fos.import_export.ui.types.' . $format,
            'icon' => 'file archive',
            'route' => \sprintf('app_export_data_%s', $resource),
            'parameters' => $parameters,
        ];
    }
}
