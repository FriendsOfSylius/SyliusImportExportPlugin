<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\TaxonomyProcessor;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
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
        $headerKeys = ['Code', 'Parent', 'Locale', 'Name', 'Slug', 'Description'];
        $dataset = ['Code' => 'root', 'Parent' => '', 'Locale' => 'en_US', 'Name' => 'Root', 'Slug' => 'root', 'Description' => 'root category'];
        $this->beConstructedWith($taxonFactory, $taxonRepository, $localeProviderInterface, $metadataValidator, $headerKeys);

        $taxonRepository->findOneBy(['code' => ''])->willReturn(null);
        $taxonRepository->findOneBy(['code' => 'root'])->willReturn(null);
        $taxonRepository->add($taxon)->shouldBeCalledTimes(1);
        $taxonFactory->createNew()->willReturn($taxon);

        $localeProviderInterface->getAvailableLocalesCodes()->willReturn(['en_US']);

        $taxon->setCurrentLocale('en_US')->shouldBeCalled();
        $taxon->setFallbackLocale('en_US')->shouldBeCalled();
        $taxon->setCode('root')->shouldBeCalled();
        $taxon->setName('Root')->shouldBeCalled();
        $taxon->setSlug('root')->shouldBeCalled();
        $taxon->setDescription('root category')->shouldBeCalled();

        $this->process($dataset);
    }
}
