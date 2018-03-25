<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePlugin;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin\ResourcePluginInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ResourcePluginSpec extends ObjectBehavior
{
    function let(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager
    ) {
        $this->beConstructedWith($repository, $propertyAccessor, $entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResourcePlugin::class);
    }

    function it_implements_the_resource_plugin_interface()
    {
        $this->shouldImplement(ResourcePluginInterface::class);
    }

    function it_loads_data_for_tax_category_on_init(
        RepositoryInterface $repository,
        TaxCategoryInterface $taxCategoryBooks,
        TaxCategoryInterface $taxCategoryCars,
        Collection $bookRates,
        Collection $carRates,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        ClassMetadata $classMetadata
    ) {
        $idsToExport = [1, 2];

        $repository->findBy(
            [
                'id' => [1, 2],
            ]
        )->willReturn(
            [
                $taxCategoryBooks,
                $taxCategoryCars,
            ]
        );

        $entityManager->getClassMetadata(Argument::type('string'))->willReturn($classMetadata);
        $classMetadata->getColumnNames()->willReturn(
            [
                'Code',
                'Name',
                'Description',
                'Rates',
                'CreatedAt',
                'UpdatedAt',
            ],
            [
                'Code',
                'Name',
                'Description',
                'Rates',
                'CreatedAt',
                'UpdatedAt',
            ]
        );

        $taxCategoryBooks->getId()->willReturn(1);
        $taxCategoryBooks->getCode()->willReturn('BOOKS');
        $taxCategoryBooks->getName()->willReturn('books');
        $taxCategoryBooks->getDescription()->willReturn('tax category for books');
        $taxCategoryBooks->getRates()->willReturn($bookRates);
        $taxCategoryBooks->getCreatedAt()->willReturn(null);
        $taxCategoryBooks->getUpdatedAt()->willReturn(null);

        $propertyAccessor->isReadable($taxCategoryBooks, 'Code')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryBooks, 'Name')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryBooks, 'Description')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryBooks, 'Rates')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryBooks, 'CreatedAt')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryBooks, 'UpdatedAt')->willReturn(true);

        $propertyAccessor->getValue($taxCategoryBooks, 'Code')->willReturn('BOOKS');
        $propertyAccessor->getValue($taxCategoryBooks, 'Name')->willReturn('books');
        $propertyAccessor->getValue($taxCategoryBooks, 'Description')->willReturn('tax category for books');
        $propertyAccessor->getValue($taxCategoryBooks, 'Rates')->willReturn($bookRates);
        $propertyAccessor->getValue($taxCategoryBooks, 'CreatedAt')->willReturn(null);
        $propertyAccessor->getValue($taxCategoryBooks, 'UpdatedAt')->willReturn(null);

        $taxCategoryCars->getId()->willReturn(2);
        $taxCategoryCars->getCode()->willReturn('CARS');
        $taxCategoryCars->getName()->willReturn('cars');
        $taxCategoryCars->getDescription()->willReturn('tax category for cars');
        $taxCategoryCars->getRates()->willReturn($carRates);
        $taxCategoryCars->getCreatedAt()->willReturn(null);
        $taxCategoryCars->getUpdatedAt()->willReturn(null);

        $propertyAccessor->isReadable($taxCategoryCars, 'Code')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryCars, 'Name')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryCars, 'Description')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryCars, 'Rates')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryCars, 'CreatedAt')->willReturn(true);
        $propertyAccessor->isReadable($taxCategoryCars, 'UpdatedAt')->willReturn(true);

        $propertyAccessor->getValue($taxCategoryCars, 'Code')->willReturn('CARS');
        $propertyAccessor->getValue($taxCategoryCars, 'Name')->willReturn('cars');
        $propertyAccessor->getValue($taxCategoryCars, 'Description')->willReturn('tax category for cars');
        $propertyAccessor->getValue($taxCategoryCars, 'Rates')->willReturn($carRates);
        $propertyAccessor->getValue($taxCategoryCars, 'CreatedAt')->willReturn(null);
        $propertyAccessor->getValue($taxCategoryCars, 'UpdatedAt')->willReturn(null);

        $this->init($idsToExport);
        $this->getData('1', ['Code', 'Name', 'Description', 'Rates'])
            ->shouldReturn([
                'Code' => 'BOOKS',
                'Name' => 'books',
                'Description' => 'tax category for books',
                'Rates' => $bookRates,
            ]);

        $this->getData('2', ['Code', 'Name', 'Description', 'Rates'])
            ->shouldReturn([
                'Code' => 'CARS',
                'Name' => 'cars',
                'Description' => 'tax category for cars',
                'Rates' => $carRates,
            ]);

        // Should error when unknown ID is asked
        $this->shouldThrow()->during('getData', ['3', ['Code', 'Name', 'Description', 'Rates']]);
    }
}
