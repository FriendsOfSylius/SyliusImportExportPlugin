<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ResourceImporter;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use PhpSpec\ObjectBehavior;
use Port\Csv\CsvReader;
use Port\Excel\ExcelReader;
use Port\Reader;
use Port\Reader\ReaderFactory;
use Prophecy\Argument;

class ResourceImporterSpec extends ObjectBehavior
{
    function let(
        ReaderFactory $readerFactory,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $this->beConstructedWith($readerFactory, $objectManager, $resourceProcessor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResourceImporter::class);
    }

    function it_implements_the_importer_interface()
    {
        $this->shouldImplement(ImporterInterface::class);
    }

    function it_throws_exception_for_reader_without_required_method(
        ReaderFactory $readerFactory,
        Reader $someReader,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($someReader);
        $this->shouldThrow(ImporterException::class)->during('import', [__DIR__ . '/tax_categories.csv']);
    }

    function it_imports_countries_from_csv_file(
        ReaderFactory $readerFactory,
        CsvReader $csvReader,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code']);
        $csvReader->rewind()->willReturn();
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'DE'],
            ['Code' => 'CH']
        );
        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/countries.csv');
    }

    function it_imports_countries_from_excel_file(
        ReaderFactory $readerFactory,
        ExcelReader $excelReader,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $excelReader->getColumnHeaders()->willReturn(['Code']);
        $excelReader->rewind()->willReturn();
        $excelReader->count()->willReturn(2);
        $excelReader->valid()->willReturn(true, true, false);
        $excelReader->next()->willReturn();
        $excelReader->current()->willReturn(
            ['Code' => 'DE'],
            ['Code' => 'CH']
        );
        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($excelReader);
        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/countries.xlsx');
    }

    function it_imports_tax_categories_from_csv_file(
        ReaderFactory $readerFactory,
        CsvReader $csvReader,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code', 'Name', 'Description']);
        $csvReader->rewind()->willReturn();
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'BOOKS', 'Name' => 'books', 'Description' => 'tax category for books'],
            ['Code' => 'CARS', 'Name' => 'cars', 'Description' => 'tax category for cars']
        );
        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/tax_categories.csv');
    }

    function it_imports_tax_categories_from_excel_file(
        ReaderFactory $readerFactory,
        ExcelReader $excelReader,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $excelReader->getColumnHeaders()->willReturn(['Code', 'Name', 'Description']);
        $excelReader->rewind()->willReturn();
        $excelReader->count()->willReturn(2);
        $excelReader->valid()->willReturn(true, true, false);
        $excelReader->next()->willReturn();
        $excelReader->current()->willReturn(
            ['Code' => 'BOOKS', 'Name' => 'books', 'Description' => 'tax category for books'],
            ['Code' => 'CARS', 'Name' => 'cars', 'Description' => 'tax category for cars']
        );
        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($excelReader);

        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/tax_categories.xlsx');
    }
}
