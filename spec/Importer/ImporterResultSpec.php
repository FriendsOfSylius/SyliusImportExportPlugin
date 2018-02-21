<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Importer;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResult;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class ImporterResultSpec extends ObjectBehavior
{
    function let(Stopwatch $stopwatch)
    {
        $this->beConstructedWith($stopwatch);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImporterResult::class);
    }

    function it_can_start_the_stopwatch(Stopwatch $stopwatch)
    {
        $stopwatch->start('import')->shouldBeCalled();
        $this->start();
    }

    function it_can_stop_the_stopwatch(Stopwatch $stopwatch)
    {
        $stopwatch->stop('import')->shouldBeCalled();
        $this->stop();
    }

    function it_can_gather_successfull_line_numbers()
    {
        $this->success(1);
        $this->success(2);
        $this->failed(3);
        $this->success(4);
        $this->skipped(5);

        $this->getSuccessRows()->shouldReturn(
            [1, 2, 4]
        );
    }

    function it_can_gather_failed_line_numbers()
    {
        $this->success(1);
        $this->success(2);
        $this->failed(3);
        $this->success(4);
        $this->skipped(5);

        $this->getFailedRows()->shouldReturn(
            [3]
        );
    }

    function it_can_gather_skipped_rows()
    {
        $this->success(1);
        $this->success(2);
        $this->failed(3);
        $this->success(4);
        $this->skipped(5);

        $this->getSkippedRows()->shouldReturn(
            [5]
        );
    }

    function it_can_return_the_duration_of_the_import(Stopwatch $stopwatch, StopwatchEvent $stopwatchEvent)
    {
        $stopwatch->stop('import')
            ->willReturn($stopwatchEvent);
        $stopwatchEvent->getDuration()->willReturn(1000.0);

        $this->stop();
        $this->getDuration()->shouldReturn(1000.0);
    }
}
