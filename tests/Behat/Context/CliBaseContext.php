<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use FriendsOfSylius\SyliusImportExportPlugin\Command\ImportDataCommand;
use PHPUnit\Framework\Assert;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class CliBaseContext implements Context
{
    /** @var array */
    protected $cliArguments = [];

    /** @var KernelInterface */
    protected $kernel;

    /** @var Application */
    protected $application;

    /** @var CommandTester */
    protected $tester;

    /** @var Command */
    protected $command;

    /** @var string */
    protected $filePath;

    /** @var RepositoryInterface */
    protected $repository;

    /**
     * @param KernelInterface     $kernel
     * @param string              $filePath
     * @param RepositoryInterface $repository
     */
    public function __construct(KernelInterface $kernel, RepositoryInterface $repository, string $filePath)
    {
        $this->kernel = $kernel;
        $this->repository = $repository;
        $this->filePath = $filePath;
    }

    /**
     * @Given I have a working command-line-interface
     */
    public function iHaveAWorkingCommandLineInterface()
    {
        $this->application = new Application($this->kernel);
    }

    /**
     * @When I import :importType data from csv-file :fileName file with the cli-command
     */
    public function iImportCsvDataFromFileWithTheCliCommand($importType, $fileName)
    {
        $this->cliArguments = [$importType, $fileName];
        $this->application->add(new ImportDataCommand());
        $this->command = $this->application->find('sylius:import');
        $this->tester = new CommandTester($this->command);
        $this->tester->execute(['command' => 'sylius:import', 'importer' => $importType, 'file' => $this->filePath . '/' . $fileName, '--format' => 'csv']);
    }

    /**
     * @Then I should see :messagePart in the output
     */
    public function iShouldSeeInTheMessage($messagePart)
    {
        Assert::assertContains($messagePart, $this->tester->getDisplay());
    }
}
