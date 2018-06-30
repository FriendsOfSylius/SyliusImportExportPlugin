<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use Enqueue\Redis\RedisConnectionFactory;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\MqItemReader;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterResultInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ImportDataCommand extends Command
{
    // if a mode is added please, accordingly, add it to $modes too
    private const MODE_IMPORT_FILE = '1';
    private const MODE_MESSAGE_QUEUE = '2';

    /**
     * @var ImporterRegistry
     */
    private $importerRegistry;

    /**
     * @param ImporterRegistry $importerRegistry
     */
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
            ->setName('sylius:import')
            ->setDescription('Import a file.')
            ->setDefinition([
                new InputArgument('importer', InputArgument::OPTIONAL, 'The importer to use.'),
                new InputArgument('file', InputArgument::OPTIONAL, 'The file to import.'),
                // @TODO try to guess the format from the file to make this optional
                new InputOption('format', null, InputOption::VALUE_OPTIONAL, 'The format of the file to import'),
                new InputOption('details', null, InputOption::VALUE_NONE,
                    'If to return details about skipped/failed rows'),
                new InputOption(
                    'mode',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    '"--mode 1" imports from file, "--mode 2" imports from Message Queue',
                    '1'
                ),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $mode = $input->getOption('mode');

        // check if given method exists
        if ($mode !== self::MODE_IMPORT_FILE && $mode !== self::MODE_MESSAGE_QUEUE) {
            $message = 'choose a mode';
            $this->listMethods($input, $output, $message);
        }

        $importer = $input->getArgument('importer');
        $format = $input->getOption('format');

        if ($mode === self::MODE_MESSAGE_QUEUE) {
            $format = 'json';
        }

        if (empty($importer) || empty($format)) {
            $message = 'choose an importer and format';
            $this->listImporters($input, $output, $message, $mode);
        }

        $name = ImporterRegistry::buildServiceName($importer, $format);
        $file = $input->getArgument('file');

        if (!$this->importerRegistry->has($name)) {
            $message = sprintf(
                "<error>There is no '%s' importer.</error>",
                $name
            );
            $output->writeln($message);

            $message = 'choose an importer and format';
            $this->listImporters($input, $output, $message, $mode);
        }

        /** @var ImporterInterface $service */
        $service = $this->importerRegistry->get($name);

        if ($mode === self::MODE_MESSAGE_QUEUE) {
            $this->getImporterJsonDataFromMessageQueue($importer, $service);
//            $this->finishImport($file, $name, $output);

            return;
        }

        $result = $service->import($file);

        $this->finishImport($file, $name, $output);

        $this->showResultDetails($input, $output, $result);
    }

    /**
     * @param string $file
     * @param string $name
     * @param OutputInterface $output
     */
    private function finishImport(string $file, string $name, OutputInterface $output): void
    {
        $message = sprintf(
            "<info>Imported '%s' via the %s importer</info>",
            $file,
            $name
        );
        $output->writeln($message);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ImporterResultInterface $result
     */
    private function showResultDetails(InputInterface $input, OutputInterface $output, ImporterResultInterface $result): void
    {
        $io = new SymfonyStyle($input, $output);

        $imported = count($result->getSuccessRows());
        $skipped = count($result->getSkippedRows());
        $failed = count($result->getFailedRows());
        $countOrRows = 'count';

        if ($input->getOption('details')) {
            $imported = implode(', ', $result->getSuccessRows());
            $skipped = implode(', ', $result->getSkippedRows());
            $failed = implode(', ', $result->getFailedRows());
            $countOrRows = 'rows';
        }

        $io->listing(
            [
                sprintf('Time taken: %s ms ', $result->getDuration()),
                sprintf('Imported %s: %s', $countOrRows, $imported),
                sprintf('Skipped %s: %s', $countOrRows, $skipped),
                sprintf('Failed %s: %s', $countOrRows, $failed),
            ]
        );
    }

    /**
     * @param string $importer
     */
    private function getImporterJsonDataFromMessageQueue(string $importer, ImporterInterface $service): void
    {
        $mqItemReader = new MqItemReader(new RedisConnectionFactory(), $service);
        $mqItemReader->initQueue('sylius.export.queue.' . $importer);
        $mqItemReader->readAndImport();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message
     * @param string $mode
     */
    private function listImporters(InputInterface $input, OutputInterface $output, string $message, string $mode): void
    {
        $output->writeln($message);
        $all = array_keys($this->importerRegistry->all());
        $importers = [];
        foreach ($all as $importer) {
            $importer = explode('.', $importer);
            $importers[$importer[0]][] = $importer[1];
        }

        $list = [];
        switch ($mode) {
            case self::MODE_IMPORT_FILE:
                $output->writeln('<info>Available importers and formats:</info>');
                foreach ($importers as $importer => $formats) {
                    $list[] = sprintf(
                        '%s (formats: %s)',
                        $importer,
                        implode(', ', $formats)
                    );
                }

                break;
            case self::MODE_MESSAGE_QUEUE:
                $output->writeln('<info>Available importers:</info>');
                foreach ($importers as $importer => $formats) {
                    $list[] = sprintf(
                        '%s',
                        $importer
                    );
                }

                break;
        }

        $io = new SymfonyStyle($input, $output);
        $io->listing($list);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message
     */
    private function listMethods(
        InputInterface $input,
        OutputInterface $output,
        string $message
    ): void {
        $modes = [
            self::MODE_IMPORT_FILE => 'Import from file',
            self::MODE_MESSAGE_QUEUE => 'Import from message queue',
        ];
        $output->writeln($message);
        $output->writeln('<info>Available modes:</info>');
        $list = [];
        foreach ($modes as $modeNumber => $modeDescription) {
            $list[] = sprintf(
                '%s (%s)',
                $modeNumber,
                $modeDescription
            );
        }

        $io = new SymfonyStyle($input, $output);
        $io->listing($list);
        exit(0);
    }
}
