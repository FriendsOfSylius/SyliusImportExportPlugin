<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Listener;

use FriendsOfSylius\SyliusImportExportPlugin\Listener\ExportButtonGridListener;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

class ExportButtonGridListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ExportButtonGridListener::class);
    }

    function let()
    {
        $this->beConstructedWith('country', ['csv', 'xlsx']);
    }

    function it_should_add_the_export_action(
        RequestStack $requestStack,
        Request $request
    ) {
        $parameters = new ParameterBag(['criteria' => ['test' => 1]]);
        $request->query = $parameters;

        $requestStack->getCurrentRequest()->willReturn($request);
        $this->setRequest($requestStack);

        $actionGroup = ActionGroup::named('main');

        $grid = Grid::fromCodeAndDriverConfiguration('country', 'doctrine', []);
        $grid->addActionGroup($actionGroup);

        $gridDefinitionConverterEvent = new GridDefinitionConverterEvent($grid);

        $this->onSyliusGridAdmin($gridDefinitionConverterEvent)->shouldReturn(null);

        $action = $actionGroup->getAction('export');
        Assert::same('fos.import_export.ui.export', $action->getLabel());
        Assert::isArray($action->getOptions()['links']);
        Assert::count($action->getOptions()['links'], 2);
    }

    function it_should_add_the_export_action_when_main_action_group_is_not_present(
        RequestStack $requestStack,
        Request $request
    ) {
        $parameters = new ParameterBag(['criteria' => ['test' => 1]]);
        $request->query = $parameters;

        $requestStack->getCurrentRequest()->willReturn($request);
        $this->setRequest($requestStack);

        $grid = Grid::fromCodeAndDriverConfiguration('country', 'doctrine', []);

        $gridDefinitionConverterEvent = new GridDefinitionConverterEvent($grid);

        $this->onSyliusGridAdmin($gridDefinitionConverterEvent)->shouldReturn(null);

        $action = $grid->getActionGroup('main')->getAction('export');
        Assert::same('fos.import_export.ui.export', $action->getLabel());
        Assert::isArray($action->getOptions()['links']);
        Assert::count($action->getOptions()['links'], 2);
    }
}
