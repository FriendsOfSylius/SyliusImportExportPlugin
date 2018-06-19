<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Writer\ExcelWriter;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\PortExcelWriterFactoryInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExcelWriterSpec extends ObjectBehavior
{
    function let(PortExcelWriterFactoryInterface $excelWriterFactory, ExcelWriter $excelWriter)
    {
        $this->beConstructedWith($excelWriterFactory);
        $excelWriterFactory->get(Argument::type('string'))->willReturn($excelWriter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ExcelWriter::class);
    }

    function it_implements_the_writer_interface()
    {
        $this->shouldImplement(WriterInterface::class);
    }

    function it_delegates_the_data_to_the_wrapped_writer(\Port\Excel\ExcelWriter $excelWriter)
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $excelWriter->prepare()->shouldBeCalled();
        $excelWriter->writeItem($data)->shouldBeCalled();
        $excelWriter->finish()->shouldBeCalled();
        $this->write($data);
        $this->finish();
    }
}
