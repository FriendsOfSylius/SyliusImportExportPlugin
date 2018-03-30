<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\HandlerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Pool;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\TransformerPoolInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

class PoolSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Pool::class);
    }

    function it_should_implement_interface()
    {
        $this->shouldImplement(TransformerPoolInterface::class);
    }

    function let(HandlerInterface $dateTimeToStringHandler)
    {
        $generator = new RewindableGenerator(function () use ($dateTimeToStringHandler) {
            return [$dateTimeToStringHandler];
        }, $count = 1);

        $this->beConstructedWith($generator);
    }

    function it_should_call_handle()
    {
        $this->handle('test', new \DateTime());
    }
}
