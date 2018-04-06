<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Writer;

use FriendsOfSylius\SyliusImportExportPlugin\Writer\PortExcelWriterFactory;
use FriendsOfSylius\SyliusImportExportPlugin\Writer\PortExcelWriterFactoryInterface;
use PhpSpec\ObjectBehavior;
use Port\Excel\ExcelWriter;

class PortExcelWriterFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PortExcelWriterFactory::class);
    }

    function it_implements_the_writer_factory_interface()
    {
        $this->shouldImplement(PortExcelWriterFactoryInterface::class);
    }

    function it_returns_class()
    {
        $this->get('test')->shouldReturnAnInstanceOf(ExcelWriter::class);
    }
}
