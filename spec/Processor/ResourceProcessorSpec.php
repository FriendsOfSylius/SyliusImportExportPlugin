<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessor;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

class ResourceProcessorSpec extends ObjectBehavior
{
    public function let(
        FactoryInterface $factory,
        RepositoryInterface $repository
    ) {
        $this->beConstructedWith($factory, $repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResourceProcessor::class);
    }

    function it_implements_the_resource_processor_interface()
    {
        $this->shouldImplement(ResourceProcessorInterface::class);
    }

    function it_can_process_an_array_of_tax_category_data(
        FactoryInterface $factory,
        TaxCategoryInterface $taxCategory,
        RepositoryInterface $repository
    ) {
        $this->beConstructedWith($factory, $repository, ['Code', 'Name', 'Description']);
        $repository->findOneBy(['code' => 'BOOKS'])->willReturn(null);
        $factory->createNew()->willReturn($taxCategory);
        $repository->add($taxCategory)->shouldBeCalledTimes(1);
        $taxCategory->setCode('BOOKS')->shouldBeCalled();
        $taxCategory->setName('books')->shouldBeCalled();
        $taxCategory->setDescription('tax category for books')->shouldBeCalled();

        $this->process(['Code' => 'BOOKS', 'Name' => 'books', 'Description' => 'tax category for books']);
    }

    function it_can_process_an_array_of_country_data(
        FactoryInterface $factory,
        TaxCategoryInterface $country,
        RepositoryInterface $repository
    ) {
        $this->beConstructedWith($factory, $repository, ['Code']);
        $repository->findOneBy(['code' => 'DE'])->willReturn(null);
        $factory->createNew()->willReturn($country);
        $repository->add($country)->shouldBeCalledTimes(1);
        $country->setCode('DE')->shouldBeCalled();

        $this->process(['Code' => 'DE']);
    }
}
