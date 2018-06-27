<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Writer\CsvWriter;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\WriterInterface;
use PhpSpec\ObjectBehavior;
use Port\Csv\CsvWriter as PortCsvWriter;
use Port\Writer;

class CsvWriterSpec extends ObjectBehavior
{
    function let(PortCsvWriter $csvWriter)
    {
        $this->beConstructedWith($csvWriter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CsvWriter::class);
    }

    function it_implements_the_writer_interface()
    {
        $this->shouldImplement(WriterInterface::class);
    }

    function it_delegates_the_data_to_the_wrapped_writer(Writer $csvWriter)
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $csvWriter->writeItem($data)->shouldBeCalled();
        $this->write($data);
    }

    function it_finishes_the_file_creation_when_we_get_the_contents(Writer $csvWriter)
    {
        $csvWriter->setCloseStreamOnFinish(true)->shouldBeCalled();
        $csvWriter->getStream()->willReturn(fopen('php://memory', 'w'));
        $csvWriter->finish()->shouldBeCalled();
        $this->getFileContent();
    }
}
