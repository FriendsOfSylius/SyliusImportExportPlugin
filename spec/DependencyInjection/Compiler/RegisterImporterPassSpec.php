<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler\RegisterImporterPass;
use FriendsOfSylius\SyliusImportExportPlugin\Listener\ImportButtonGridListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterImporterPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass(): void
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RegisterImporterPass::class);
    }

    function it_processes_the_importer_services(
        ContainerBuilder $container,
        Definition $importerRegistry,
        Definition $importButtonListenerDefinition
    ) {
        $importerType = 'csv';
        /**
         * prepare the mock for the container builder
         */
        $container->getParameter('sylius.importer.web_ui')->willReturn(true);
        $container->has('sylius.importers_registry')->willReturn(true);
        $container->has(Argument::type('string'))->willReturn(false);
        $container->findDefinition('sylius.importers_registry')->willReturn($importerRegistry);
        $container->findTaggedServiceIds('sylius.importer')->willReturn([
            'importer_id' => [
                [
                    'type' => $importerType,
                    'format' => 'importer_format',
                ],
            ],
        ]);
        $container->register(
            Argument::type('string'),
            ImportButtonGridListener::class
        )->willReturn($importButtonListenerDefinition)->shouldBeCalled();

        /**
         * prepare the mock for the importerRegistry
         */
        $importerRegistry->addMethodCall(
            'register',
            Argument::type('array')
        )->shouldBeCalled();

        /**
         * prepare the mock for the definition of the sonata-event
         */
        $importButtonListenerDefinition->setAutowired(false)
            ->shouldBeCalled()
            ->willReturn($importButtonListenerDefinition);

        $importButtonListenerDefinition->addArgument(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn($importButtonListenerDefinition);

        $importButtonListenerDefinition->addTag(
                'kernel.event_listener',
                [
                    'event' => 'sylius.grid.admin_' . $importerType,
                    'method' => 'onSyliusGridAdmin',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($importButtonListenerDefinition);

        /**
         * run the test
         */
        $this->process($container);
    }
}
