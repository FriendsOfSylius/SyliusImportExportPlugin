<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler\ArrayToStringHandler;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\HandlerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Pool;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Webmozart\Assert\Assert;

class ArrayToStringHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ArrayToStringHandler::class);
    }

    function it_extends()
    {
        $this->shouldHaveType(Handler::class);
    }

    function it_should_implement()
    {
        $this->shouldImplement(HandlerInterface::class);
    }

    function it_should_process_directly()
    {
        $array = ['a', 'b', 'c'];
        $this->handle('test', $array)->shouldBeString();
        $this->handle('test', $array)->shouldBe('a|b|c');
    }

    function it_should_process_via_pool()
    {
        $array = ['a', 'b', 'c'];

        $generator = new RewindableGenerator(function () {
            yield $this->getWrappedObject();
        }, $count = 1);

        $pool = new Pool($generator);

        $result = $pool->handle('test', $array);

        Assert::same('a|b|c', $result);
    }
}
