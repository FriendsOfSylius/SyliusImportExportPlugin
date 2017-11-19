<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Processor;

use FriendsOfSylius\SyliusImportExportPlugin\Exception\ItemIncompleteException;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidator;
use FriendsOfSylius\SyliusImportExportPlugin\Processor\MetadataValidatorInterface;
use PhpSpec\ObjectBehavior;

class MetadataValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MetadataValidator::class);
    }

    function it_implements_the_metadata_validator_interface()
    {
        $this->shouldImplement(MetadataValidatorInterface::class);
    }

    function it_can_validate_the_presence_of_header_keys_in_given_dataset_array_with_exact_these_keys()
    {
        $headerKeys = ['headerKey1', 'headerKey2', 'headerKey3'];
        $dataset = ['headerKey1' => 'value1', 'headerKey2' => 'value2', 'headerKey3' => 'value3'];
        $this
            ->shouldNotThrow(ItemIncompleteException::class)
            ->during('validateHeaders', [$headerKeys, $dataset]);
    }

    function it_can_validate_the_presence_of_header_keys_in_given_dataset_array_with_additional_keys()
    {
        $headerKeys = ['headerKey1', 'headerKey2', 'headerKey3'];
        $dataset = ['headerKey1' => 'value1', 'headerKey2' => 'value2', 'headerKey3' => 'value3', 'headerKey4' => 'value4'];
        $this
            ->shouldNotThrow(ItemIncompleteException::class)
            ->during('validateHeaders', [$headerKeys, $dataset]);
    }

    function it_can_validate_the_absence_of_header_keys_in_given_dataset_array()
    {
        $headerKeys = ['headerKey1', 'headerKey2', 'headerKey3'];
        $dataset = ['WrongheaderKey1' => 'value1', 'headerKey2' => 'value2', 'headerKey3' => 'value3'];
        $this
            ->shouldThrow(
                new ItemIncompleteException(
                    sprintf(
                        'The mandatory header-keys, "%s", are missing in the data-set. Found header-keys are "%s". ' .
                        'Either change the service definition of the processor accordingly or update your import-data',
                        'headerKey1',
                        implode(', ', array_keys($dataset))
                    )
                )
            )
            ->during('validateHeaders', [$headerKeys, $dataset]);
    }
}
