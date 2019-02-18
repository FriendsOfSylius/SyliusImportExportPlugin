<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Writer\PortSpreadsheetWriterFactory;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\PortSpreadsheetWriterFactoryInterface;
use PhpSpec\ObjectBehavior;
use Port\Spreadsheet\SpreadsheetWriter;

class PortSpreadsheetWriterFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PortSpreadsheetWriterFactory::class);
    }

    function it_implements_the_writer_factory_interface()
    {
        $this->shouldImplement(PortSpreadsheetWriterFactoryInterface::class);
    }

    function it_returns_class()
    {
        $this->get('test')->shouldReturnAnInstanceOf(SpreadsheetWriter::class);
    }
}
