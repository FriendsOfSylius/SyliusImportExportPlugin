<?php

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\TaxonomyProcessor;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

class TaxonomyProcessorSpec extends ObjectBehavior
{
    public function let(
        TaxonFactoryInterface $taxonFactory,
        TaxonRepositoryInterface $taxonRepository,
        LocaleProviderInterface $localeProviderInterface,
        MetadataValidatorInterface $metadataValidator
    ) {
        $this->beConstructedWith($taxonFactory, $taxonRepository, $localeProviderInterface, $metadataValidator, []);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TaxonomyProcessor::class);
    }

    public function it_implements_the_resource_processor_interface()
    {
        $this->shouldImplement(ResourceProcessorInterface::class);
    }

    public function it_can_process_an_array_of_taxonomies_data(
        TaxonFactoryInterface $taxonFactory,
        TaxonInterface $taxon,
        TaxonRepositoryInterface $taxonRepository,
        LocaleProviderInterface $localeProviderInterface,
        MetadataValidatorInterface $metadataValidator
    ) {
        $headerKeys = ['Code','Parent','Locale','Name','Slug','Description'];
        $dataset = ['Code' => 'root', 'Parent' => '', 'Locale' => 'en_US', 'Name' => 'Root', 'root', 'Description' => 'root category'];
        $this->beConstructedWith($taxonFactory, $taxonRepository, $localeProviderInterface, $metadataValidator, $headerKeys);

        $taxonFactory->findOneBy(['code' => 'root'])->willReturn(null);
        $taxonFactory->add($taxon)->shouldBeCalledTimes(1);
        $taxonFactory->createNew()->willReturn($taxon);

        $this->process($dataset);
    }
}
