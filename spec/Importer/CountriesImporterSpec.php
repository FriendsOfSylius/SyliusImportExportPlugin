<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
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

    function it_imports_data_of_countries_pass_in_csv_file(
        CsvReader $csvReader,
        CsvReaderFactory $csvReaderFactory,
        ObjectManager $countryManager,
        FactoryInterface $countryFactory,
        RepositoryInterface $countryRepository,
        CountryInterface $countryOne,
        CountryInterface $countryTwo
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
}
