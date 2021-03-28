<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ItemReaderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\SingleDataArrayImporterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class ImportDataFromMessageQueueCommand extends Command
{
    use ContainerAwareTrait;

    /** @var ImporterRegistry */
    private $importerRegistry;

    public function __construct(ImporterRegistry $importerRegistry)
    {
        $this->importerRegistry = $importerRegistry;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('sylius:import-from-message-queue')
            ->setDescription('Import data from message queue.')
            ->setDefinition([
                new InputArgument('importer', InputArgument::OPTIONAL, 'The importer to use.'),
                new InputOption('timeout', 't', InputOption::VALUE_OPTIONAL, 'The time in ms the importer will wait for some input.', '0'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $importer */
        $importer = $input->getArgument('importer');

        if ('' === $importer) {
            $this->listImporters($input, $output);

            return 0;
        }

        /** @var string $timeout */
        $timeout = $input->getOption('timeout');

        // only accepts the format of json as messages
        $name = ImporterRegistry::buildServiceName($importer, 'json');

        if (!$this->importerRegistry->has($name)) {
            $message = sprintf(
                "<error>There is no '%s' importer.</error>",
                $name
            );

            $this->listImporters($input, $output, $message);
        }

        /** @var SingleDataArrayImporterInterface $service */
        $service = $this->importerRegistry->get($name);

        /** @var ItemReaderInterface $mqItemReader */
        $mqItemReader = $this->container->get('sylius.message_queue_reader');
        $this->importJsonDataFromMessageQueue($mqItemReader, $importer, $service, $output, (int) $timeout);
        $this->finishImport($name, $output);

        return 0;
    }

    private function finishImport(string $name, OutputInterface $output): void
    {
        $message = sprintf(
            '<info>Imported from the message queue via the %s importer</info>',
            $name
        );
        $output->writeln($message);
    }

    private function importJsonDataFromMessageQueue(ItemReaderInterface $mqItemReader, string $importer, SingleDataArrayImporterInterface $service, OutputInterface $output, int $timeout): void
    {
        $mqItemReader->initQueue('sylius.export.queue.' . $importer);
        $mqItemReader->readAndImport($service, $timeout);
        $output->writeln('Imported: ' . $mqItemReader->getMessagesImportedCount());
        $output->writeln('Skipped: ' . $mqItemReader->getMessagesSkippedCount());
    }

    private function listImporters(InputInterface $input, OutputInterface $output, ?string $errorMessage = null): void
    {
        $all = array_keys($this->importerRegistry->all());
        $importers = [];
        foreach ($all as $importer) {
            $importer = explode('.', $importer);
            $format = \array_pop($importer);
            $type = \implode('.', $importer);

            $importers[$type][] = $format;
        }

        $list = [];
        $output->writeln('<info>Available importers:</info>');
        foreach ($importers as $importer => $formats) {
            $list[] = sprintf(
                '%s',
                $importer
            );
        }

        $io = new SymfonyStyle($input, $output);
        $io->listing($list);

        if (null !== $errorMessage) {
            throw new \RuntimeException($errorMessage);
        }
    }
}
