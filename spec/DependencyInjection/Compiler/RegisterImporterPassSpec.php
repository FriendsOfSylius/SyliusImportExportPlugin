<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler;

use FriendsOfSylius\SyliusImportExportPlugin\DependencyInjection\Compiler\RegisterImporterPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\UiBundle\Block\BlockEventListener;
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
        Definition $blockEventDefinition
    ) {
        $importerType = 'csv';
        /**
         * prepare the mock for the container builder
         */
        $container->has('sylius.importers_registry')->willReturn(true);
        $container->findDefinition('sylius.importers_registry')->willReturn($importerRegistry);
        $container->findTaggedServiceIds('sylius.importer')->willReturn([
            'importer_id' => [
                [
                    'type' => $importerType,
                    'format' => 'importer_format'
                ]
            ]
        ]);
        $container->register(
            Argument::type('string'),
            BlockEventListener::class
        )->willReturn($blockEventDefinition)->shouldBeCalled();

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
        $blockEventDefinition->setAutowired(false)
            ->shouldBeCalled()
            ->willReturn($blockEventDefinition);

        $blockEventDefinition->addArgument(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn($blockEventDefinition);

        $blockEventDefinition->addTag(
            'kernel.event_listener',
                [
                    'event' => 'sonata.block.event.sylius.admin.' . $importerType . '.index.after_content',
                    'method' => 'onBlockEvent'
                ]
        )
            ->shouldBeCalled()
            ->willReturn($blockEventDefinition);

        /**
         * run the test
         */
        $this->process($container);
    }
}
