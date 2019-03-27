<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResultInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\JsonResourceImporter;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ResourceImporter;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\ResourceProcessorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JsonResourceImporterSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult
    ) {
        $this->beConstructedWith($objectManager, $resourceProcessor, $importerResult, false, false, false);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JsonResourceImporter::class);
    }

    function it_implements_the_importer_interface()
    {
        $this->shouldImplement(ResourceImporter::class);
    }

    function it_imports_countries_from_json_file(
        ObjectManager $objectManager,
        ResourceProcessorInterface $resourceProcessor,
        ImporterResultInterface $importerResult
    ) {
        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/countries.json')->shouldReturn($importerResult);
    }

    function it_imports_tax_categories_from_json_file(
        ObjectManager $objectManager,
        ImporterResultInterface $importerResult,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/tax_categories.json')->shouldReturn($importerResult);
    }

    function it_imports_customer_groups_from_json_file(
        ObjectManager $objectManager,
        ImporterResultInterface $importerResult,
        ResourceProcessorInterface $resourceProcessor
    ) {
        $resourceProcessor->process(Argument::type('array'))->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalledTimes(1);

        $importerResult->start()->shouldBeCalledTimes(1);
        $importerResult->success(Argument::type('int'))->shouldBeCalledTimes(2);
        $importerResult->stop()->shouldBeCalledTimes(1);

        $this->import(__DIR__ . '/customer_groups.json')->shouldReturn($importerResult);
    }
}
