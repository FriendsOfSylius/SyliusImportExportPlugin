<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Writer\PortSpreadsheetWriterFactoryInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\SpreadsheetWriter;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SpreadsheetWriterSpec extends ObjectBehavior
{
    function let(PortSpreadsheetWriterFactoryInterface $spreadsheetWriterFactory, SpreadsheetWriter $spreadsheetWriter)
    {
        $this->beConstructedWith($spreadsheetWriterFactory);
        $spreadsheetWriterFactory->get(Argument::type('string'))->willReturn($spreadsheetWriter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SpreadsheetWriter::class);
    }

    function it_implements_the_writer_interface()
    {
        $this->shouldImplement(WriterInterface::class);
    }

    function it_delegates_the_data_to_the_wrapped_writer(\Port\Spreadsheet\SpreadsheetWriter $spreadsheetWriter)
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $spreadsheetWriter->prepare()->shouldBeCalled();
        $spreadsheetWriter->writeItem($data)->shouldBeCalled();
        $spreadsheetWriter->finish()->shouldBeCalled();
        $this->write($data);
        $this->finish();
    }
}
