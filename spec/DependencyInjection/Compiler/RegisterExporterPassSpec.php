<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler\RegisterExporterPass;
use FriendsOfSylius\SyliusImportExportPlugin\Listener\ExportButtonGridListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterExporterPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass(): void
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RegisterExporterPass::class);
    }

    function it_processes_the_exporter_services(
        ContainerBuilder $container,
        Definition $exporterRegistry,
        Definition $blockEventDefinition
    ) {
        $exporterType = 'csv';

        $container->has('sylius.exporters_registry')->willReturn(true);

        $container->findDefinition('sylius.exporters_registry')->willReturn($exporterRegistry);

        $container->findTaggedServiceIds('sylius.exporter')->willReturn([
            'exporter_id' => [
                [
                    'type' => $exporterType,
                    'format' => 'exporter_format',
                ],
            ],
        ]);

        $container->getParameter('sylius.exporter.web_ui')->willReturn(true);
        $container->has('app.grid_event_listener.admin.crud_csv_exporter_format_export')->willReturn(false);

        $container->register(
            'app.grid_event_listener.admin.crud_csv_exporter_format_export',
            ExportButtonGridListener::class
        )->willReturn($blockEventDefinition);

        $blockEventDefinition->setAutowired(false)->willReturn($blockEventDefinition);

        $blockEventDefinition->addArgument(['exporter_format'])->willReturn($blockEventDefinition);
        $blockEventDefinition->addArgument('csv')->willReturn($blockEventDefinition);
        $blockEventDefinition->addTag('kernel.event_listener',
            [
                'event' => 'sylius.grid.admin_csv',
                'method' => 'onSyliusGridAdmin',
            ])->willReturn($blockEventDefinition);

       $exporterRegistry->addMethodCall(
           'register',
           Argument::type('array')
       )->shouldBeCalled();

        /**
         * run the test
         */
        $this->process($container);
    }
}
