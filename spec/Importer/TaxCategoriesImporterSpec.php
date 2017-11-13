<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\TaxCategoriesImporter;
use PhpSpec\ObjectBehavior;
use Port\Csv\CsvReader;
use Port\Csv\CsvReaderFactory;
use Prophecy\Argument;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

class TaxCategoriesImporterSpec extends ObjectBehavior
{
    function let(
        CsvReaderFactory $csvReaderFactory,
        FactoryInterface $taxCategoryFactory,
        RepositoryInterface $taxCategoryRepository,
        ObjectManager $taxCategoryManager
    ) {
        $this->beConstructedWith($csvReaderFactory, $taxCategoryFactory, $taxCategoryRepository, $taxCategoryManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TaxCategoriesImporter::class);
    }

    function it_implements_importer_interface()
    {
        $this->shouldImplement(ImporterInterface::class);
    }

    function it_imports_data_of_tax_categories_pass_in_csv_file(
        CsvReaderFactory $csvReaderFactory,
        CsvReader $csvReader,
        ObjectManager $taxCategoryManager,
        FactoryInterface $taxCategoryFactory,
        RepositoryInterface $taxCategoryRepository,
        TaxCategoryInterface $taxCategoryBook,
        TaxCategoryInterface $taxCategoryCar
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code', 'Name', 'Description']);
        $csvReader->key()->willReturn(0, 1);
        $csvReader->rewind()->willReturn();
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'BOOKS', 'Name' => 'books', 'Description' => 'tax category for books'],
            ['Code' => 'CARS', 'Name' => 'cars', 'Description' => 'tax category for cars']
        );

        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $taxCategoryRepository->findOneBy(['code' => 'BOOKS'])->willReturn(null);
        $taxCategoryRepository->findOneBy(['code' => 'CARS'])->willReturn(null);

        $taxCategoryFactory->createNew()->willReturn($taxCategoryBook, $taxCategoryCar);

        $taxCategoryBook->setCode('BOOKS')->shouldBeCalled();
        $taxCategoryBook->setName('books')->shouldBeCalled();
        $taxCategoryBook->setDescription('tax category for books')->shouldBeCalled();

        $taxCategoryCar->setCode('CARS')->shouldBeCalled();
        $taxCategoryCar->setName('cars')->shouldBeCalled();
        $taxCategoryCar->setDescription('tax category for cars')->shouldBeCalled();

        $taxCategoryManager->persist($taxCategoryBook)->shouldBeCalled();
        $taxCategoryManager->persist($taxCategoryCar)->shouldBeCalled();

        $taxCategoryManager->flush()->shouldBeCalled();

        $this->import(__DIR__ . '/tax_categories.csv');
    }

    function it_updates_existing_tax_category_data(
        CsvReaderFactory $csvReaderFactory,
        CsvReader $csvReader,
        ObjectManager $taxCategoryManager,
        FactoryInterface $taxCategoryFactory,
        RepositoryInterface $taxCategoryRepository,
        TaxCategoryInterface $taxCategoryBook,
        TaxCategoryInterface $taxCategoryCar
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code', 'Name', 'Description']);
        $csvReader->key()->willReturn(0, 1);
        $csvReader->rewind()->willReturn();
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'BOOKS', 'Name' => 'books', 'Description' => 'tax category for books'],
            ['Code' => 'CARS', 'Name' => 'cars', 'Description' => 'tax category for cars']
        );

        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $taxCategoryRepository->findOneBy(['code' => 'BOOKS'])->willReturn(null);
        $taxCategoryRepository->findOneBy(['code' => 'CARS'])->willReturn($taxCategoryCar);

        $taxCategoryFactory->createNew()->willReturn($taxCategoryBook);

        $taxCategoryBook->setCode('BOOKS')->shouldBeCalled();
        $taxCategoryBook->setName('books')->shouldBeCalled();
        $taxCategoryBook->setDescription('tax category for books')->shouldBeCalled();

        $taxCategoryCar->setName('cars')->shouldBeCalled();
        $taxCategoryCar->setDescription('tax category for cars')->shouldBeCalled();

        $taxCategoryManager->persist($taxCategoryBook)->shouldBeCalled();

        $taxCategoryManager->flush()->shouldBeCalled();

        $this->import(__DIR__ . '/tax_categories.csv');
    }

    function it_fails_importing_data_of_tax_categories_for_missing_headers_in_csv_file(
        CsvReaderFactory $csvReaderFactory,
        CsvReader $csvReader
    ) {
        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $csvReader->getColumnHeaders()->willReturn([]);

        $this
            ->shouldThrow(ImporterException::class)
            ->during('import', [__DIR__ . '/tax_categories.csv'])
        ;
    }
}
