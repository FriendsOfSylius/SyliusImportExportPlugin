<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResultInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ResourceImporter;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use PhpSpec\ObjectBehavior;
use Port\Csv\CsvReader;
use Port\Reader\ReaderFactory;
use Port\Spreadsheet\SpreadsheetReader;
use Prophecy\Argument;

class ResourceImporterSpec extends ObjectBehavior
{
    function let(
        ReaderFactory $readerFactory,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult
    ) {
        $this->beConstructedWith($readerFactory, $objectManager, $resourceProcessor, $importerResult, false, false, false);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResourceImporter::class);
    }

    function it_implements_the_importer_interface()
    {
        $this->shouldImplement(ImporterInterface::class);
    }

    function it_imports_countries_from_csv_file(
        ReaderFactory $readerFactory,
        CsvReader $csvReader,
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code']);
        $csvReader->rewind()->willReturn();
        $csvReader->key()->willReturn(0, 1);
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

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/countries.csv')->shouldReturn($importerResult);
    }

    function it_imports_countries_from_spreadsheet_file(
        ReaderFactory $readerFactory,
        SpreadsheetReader $spreadsheetReader,
        ObjectManager $objectManager,
        ImporterResultInterface $importerResult,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $spreadsheetReader->rewind()->willReturn();
        $spreadsheetReader->key()->willReturn(0, 1);
        $spreadsheetReader->count()->willReturn(2);
        $spreadsheetReader->valid()->willReturn(true, true, false);
        $spreadsheetReader->next()->willReturn();
        $spreadsheetReader->current()->willReturn(
            ['Code' => 'DE'],
            ['Code' => 'CH']
        );
        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($spreadsheetReader);
        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/countries.xlsx')->shouldReturn($importerResult);
    }

    function it_imports_tax_categories_from_csv_file(
        ReaderFactory $readerFactory,
        CsvReader $csvReader,
        ObjectManager $objectManager,
        ImporterResultInterface $importerResult,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $csvReader->rewind()->willReturn();
        $csvReader->key()->willReturn(0, 1);
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

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/tax_categories.csv')->shouldReturn($importerResult);
    }

    function it_imports_tax_categories_from_spreadsheet_file(
        ReaderFactory $readerFactory,
        SpreadsheetReader $spreadsheetReader,
        ObjectManager $objectManager,
        ImporterResultInterface $importerResult,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $spreadsheetReader->rewind()->willReturn();
        $spreadsheetReader->key()->willReturn(0, 1);
        $spreadsheetReader->count()->willReturn(2);
        $spreadsheetReader->valid()->willReturn(true, true, false);
        $spreadsheetReader->next()->willReturn();
        $spreadsheetReader->current()->willReturn(
            ['Code' => 'BOOKS', 'Name' => 'books', 'Description' => 'tax category for books'],
            ['Code' => 'CARS', 'Name' => 'cars', 'Description' => 'tax category for cars']
        );
        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($spreadsheetReader);

        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/tax_categories.xlsx')->shouldReturn($importerResult);
    }

    function it_imports_customer_groups_from_csv_file(
        ReaderFactory $readerFactory,
        CsvReader $csvReader,
        ObjectManager $objectManager,
        ImporterResultInterface $importerResult,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $csvReader->rewind()->willReturn();
        $csvReader->key()->willReturn(0, 1);
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'PREMIUM', 'Name' => 'premium'],
            ['Code' => 'BASIC', 'Name' => 'basic']
        );

        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/customer_groups.csv')->shouldReturn($importerResult);
    }

    function it_imports_customer_groups_from_spreadsheet_file(
        ReaderFactory $readerFactory,
        SpreadsheetReader $spreadsheetReader,
        ObjectManager $objectManager,
        ImporterResultInterface $importerResult,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $spreadsheetReader->rewind()->willReturn();
        $spreadsheetReader->key()->willReturn(0, 1);
        $spreadsheetReader->count()->willReturn(2);
        $spreadsheetReader->valid()->willReturn(true, true, false);
        $spreadsheetReader->next()->willReturn();
        $spreadsheetReader->current()->willReturn(
            ['Code' => 'PREMIUM', 'Name' => 'premium'],
            ['Code' => 'BASIC', 'Name' => 'basic']
        );
        $readerFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($spreadsheetReader);

        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/customer_groups.xlsx')->shouldReturn($importerResult);
    }
}
