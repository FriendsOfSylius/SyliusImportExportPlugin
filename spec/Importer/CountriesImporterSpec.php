<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\CountriesImporter;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use PhpSpec\ObjectBehavior;
use Port\Csv\CsvReader;
use Port\Csv\CsvReaderFactory;
use Prophecy\Argument;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class CountriesImporterSpec extends ObjectBehavior
{
    function let(
        CsvReaderFactory $csvReaderFactory,
        FactoryInterface $countryFactory,
        RepositoryInterface $countryRepository,
        ObjectManager $countryManager
    ) {
        $this->beConstructedWith($csvReaderFactory, $countryFactory, $countryRepository, $countryManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountriesImporter::class);
    }

    function it_implements_importer_interface()
    {
        $this->shouldImplement(ImporterInterface::class);
    }

    function it_imports_data_of_countries_passed_in_csv_file(
        CsvReader $csvReader,
        CsvReaderFactory $csvReaderFactory,
        ObjectManager $countryManager,
        FactoryInterface $countryFactory,
        RepositoryInterface $countryRepository,
        CountryInterface $countryOne,
        CountryInterface $countryTwo
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code']);
        $csvReader->key()->willReturn(0, 1);
        $csvReader->rewind()->willReturn();
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'DE'],
            ['Code' => 'CH']
        );
        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))
            ->willReturn($csvReader);

        $countryRepository->findOneBy(['code' => 'DE'])->willReturn(null);
        $countryRepository->findOneBy(['code' => 'CH'])->willReturn(null);

        $countryFactory->createNew()->willReturn($countryOne, $countryTwo);

        $countryOne->setCode('DE')->shouldBeCalled();
        $countryTwo->setCode('CH')->shouldBeCalled();

        $countryManager->persist($countryOne)->shouldBeCalledTimes(1);
        $countryManager->persist($countryTwo)->shouldBeCalledTimes(1);
        $countryManager->flush()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/countries.csv');
    }

    function it_updates_existing_countries_data(
        CsvReaderFactory $csvReaderFactory,
        CsvReader $csvReader,
        ObjectManager $countryManager,
        FactoryInterface $countryFactory,
        RepositoryInterface $countryRepository,
        CountryInterface $countryOne,
        CountryInterface $countryTwo
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code']);
        $csvReader->key()->willReturn(0, 1);
        $csvReader->rewind()->willReturn();
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'DE'],
            ['Code' => 'CH']
        );

        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $countryRepository->findOneBy(['code' => 'DE'])->willReturn(null);
        $countryRepository->findOneBy(['code' => 'CH'])->willReturn($countryTwo);

        $countryFactory->createNew()->willReturn($countryOne);

        $countryOne->setCode('DE')->shouldBeCalled();

        $countryManager->persist($countryOne)->shouldBeCalled();

        $countryManager->flush()->shouldBeCalled();

        $this->import(__DIR__ . '/countries.csv');
    }

    function it_fails_importing_data_of_countries_for_missing_headers_in_csv_file(
        CsvReaderFactory $csvReaderFactory,
        CsvReader $csvReader
    ) {
        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $csvReader->getColumnHeaders()->willReturn([]);

        $this
            ->shouldThrow(ImporterException::class)
            ->during('import', [__DIR__ . '/countries.csv'])
        ;
    }
}
