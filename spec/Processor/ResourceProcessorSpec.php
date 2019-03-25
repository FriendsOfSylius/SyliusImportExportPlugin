<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\AccessorNotFoundException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessor;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ResourceProcessorSpec extends ObjectBehavior
{
    public function let(
        FactoryInterface $factory,
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->beConstructedWith($factory, $repository, $propertyAccessor, $metadataValidator, $entityManager, []);
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
        PropertyAccessorInterface $propertyAccessor,
        RepositoryInterface $repository,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager
    ) {
        $headerKeys = ['Code', 'Name', 'Description'];
        $dataset = ['Code' => 'BOOKS', 'Name' => 'books', 'Description' => 'tax category for books'];

        $this->beConstructedWith($factory, $repository, $propertyAccessor, $metadataValidator, $entityManager, $headerKeys);

        $metadataValidator->validateHeaders($headerKeys, $dataset)->shouldBeCalled();

        $repository->findOneBy(['code' => 'BOOKS'])->willReturn(null);
        $entityManager->persist($taxCategory)->shouldBeCalledTimes(1);
        $factory->createNew()->willReturn($taxCategory);

        $propertyAccessor->isReadable($taxCategory, 'Code')
            ->willReturn(true)
            ->shouldBeCalled();
        $propertyAccessor->isReadable($taxCategory, 'Name')
            ->willReturn(true)
            ->shouldBeCalled();
        $propertyAccessor->isReadable($taxCategory, 'Description')
            ->willReturn(true)
            ->shouldBeCalled();

        $propertyAccessor->setValue($taxCategory, 'Code', 'BOOKS')->shouldBeCalled();
        $propertyAccessor->setValue($taxCategory, 'Name', 'books')->shouldBeCalled();
        $propertyAccessor->setValue($taxCategory, 'Description', 'tax category for books')->shouldBeCalled();

        $this->process($dataset);
    }

    function it_can_process_an_array_of_country_data(
        FactoryInterface $factory,
        TaxCategoryInterface $country,
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager
    ) {
        $headerKeys = ['Code'];
        $dataset = ['Code' => 'DE'];

        $this->beConstructedWith($factory, $repository, $propertyAccessor, $metadataValidator, $entityManager, $headerKeys);

        $metadataValidator->validateHeaders($headerKeys, $dataset)->shouldBeCalled();

        $repository->findOneBy(['code' => 'DE'])->willReturn(null);
        $entityManager->persist($country)->shouldBeCalledTimes(1);

        $factory->createNew()->willReturn($country);

        $propertyAccessor->isReadable($country, 'Code')
            ->willReturn(true)
            ->shouldBeCalled();
        $propertyAccessor->setValue($country, 'Code', 'DE')
            ->shouldBeCalled();

        $this->process($dataset);
        $entityManager->flush();
    }

    function it_throws_accessor_not_found_exception_for_non_existing_header_keys(
        FactoryInterface $factory,
        TaxCategoryInterface $country,
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        MetadataValidatorInterface $metadataValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->beConstructedWith($factory, $repository, $propertyAccessor, $metadataValidator, $entityManager, ['Code', 'not_existing_header_key']);
        $repository->findOneBy(['code' => 'DE'])->willReturn(null);
        $factory->createNew()->willReturn($country);
        $repository->add($country)->shouldNotBeCalled();

        $propertyAccessor->isReadable($country, 'Code')
            ->willReturn(true)
            ->shouldBeCalled();
        $propertyAccessor->isReadable($country, 'not_existing_header_key')
            ->willReturn(false)
            ->shouldBeCalled();

        $propertyAccessor->setValue($country, 'Code', 'DE')
            ->shouldBeCalled();

        $this->shouldThrow(
            new AccessorNotFoundException(
                sprintf(
                    'No Accessor found for %s in Resource %s, please implement one or change the Header-Key to an existing field',
                    'not_existing_header_key',
                    get_class($country->getWrappedObject())
                )
            )
        )->during('process', [['Code' => 'DE', 'not_existing_header_key' => 'some value']]);
    }
}
