<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler\RegisterExporterPass;
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
        /**
         * prepare the mock for the container builder
         */
        $container->getParameter('sylius.importer.web_ui')->willReturn(true);
        $container->has('sylius.exporters_registry')->willReturn(true);
        $container->has(Argument::type('string'))->willReturn(false);
        $container->findDefinition('sylius.exporters_registry')->willReturn($exporterRegistry);
        $container->findTaggedServiceIds('sylius.exporter')->willReturn([
            'exporter_id' => [
                [
                    'type' => $exporterType,
                    'format' => 'exporter_format',
                ],
            ],
        ]);

        /**
         * prepare the mock for the exporterRegistry
         */
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
