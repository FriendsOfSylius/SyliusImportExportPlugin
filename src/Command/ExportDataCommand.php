<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use Enqueue\Redis\RedisConnectionFactory;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\MqItemWriter;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ResourceExporterInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class ExportDataCommand extends Command
{
    use ContainerAwareTrait;

    /**
     * @var ExporterRegistry
     */
    private $exporterRegistry;

    /**
     * @param ExporterRegistry $exporterRegistry
     */
    public function __construct(ExporterRegistry $exporterRegistry)
    {
        $this->exporterRegistry = $exporterRegistry;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('sylius:export')
            ->setDescription('Export data to a file.')
            ->setDefinition([
                new InputArgument('exporter', InputArgument::OPTIONAL, 'The exporter to use.'),
                new InputArgument('file', InputArgument::OPTIONAL, 'The target file to export to.'),
                new InputOption('format', null, InputOption::VALUE_OPTIONAL, 'The format of the file to export to'),
                /** @todo Extracting details to show with this option. At the moment it will have no effect */
                new InputOption('details', null, InputOption::VALUE_NONE,
                    'If to return details about skipped/failed rows'),
                new InputOption('mode', null, InputOption::VALUE_OPTIONAL, '"--mode 1" creates file, "--mode 2" writes to Message Queue'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mode = $input->getOption('mode');

        $modes = [
            '1' => 'Create file',
            '2' => 'Write to message queue',
        ];

        // check if given method exists
        if (!empty($mode) && (!array_key_exists($mode, $modes))) {
            $message = 'choose a mode';
            $this->listMethods($input, $output, $message, $modes);
        }

        // set mode by default to 1
        $mode = $mode ?: '1';

        $exporter = $input->getArgument('exporter');
        $format = $input->getOption('format');

        if (empty($exporter) || empty($format)) {
            $message = 'choose an exporter and format';
            $this->listExporters($input, $output, $message, $mode);
        }

        /** @var RepositoryInterface $repository */
        $repository = $this->container->get('sylius.repository.' . $exporter);
        $allItems = $repository->findAll();

        if ($mode === '2') {
            $this->exportToMq($allItems);
            $this->finishExport($allItems, 'the message queue', $exporter, $output);

            return 0; // finally got a early return done
        }

        $name = ExporterRegistry::buildServiceName('sylius.' . $exporter, $format);

        if (!$this->exporterRegistry->has($name)) {
            $message = sprintf(
                "<error>There is no '%s' exporter.</error>",
                $name
            );

            $this->listExporters($input, $output, $message, $mode);
        }

        $file = $input->getArgument('file');

        /** @var array $idsToExport */
        $idsToExport = $this->prepareExport($allItems);
        $this->exportToFile($name, $file, $idsToExport);
        $this->finishExport($allItems, $file, $name, $output);
    }

    /**
     * @param array $allItems
     *
     * @return array
     */
    public function prepareExport(array $allItems): array
    {
        $idsToExport = [];
        foreach ($allItems as $item) {
            /** @var ResourceInterface $item */
            $idsToExport[] = $item->getId();
        }

        return $idsToExport;
    }

    /**
     * @param string $name
     * @param string $file
     * @param array $idsToExport
     */
    public function exportToFile(string $name, string $file, array $idsToExport): void
    {
        /** @var ResourceExporterInterface $service */
        $service = $this->exporterRegistry->get($name);
        $service->setExportFile($file);

        $service->export($idsToExport);
    }

    public function exportToMq(array $allItems): void
    {
        // insert mq logic here
        $mqItemWriter = new MqItemWriter(new RedisConnectionFactory());
        $mqItemWriter->initQueue('sylius.export.queue');
        $mqItemWriter->write($allItems);
    }

    /**
     * @param array $allItems
     * @param string $file
     * @param string $name
     * @param OutputInterface $output
     */
    public function finishExport(array $allItems, string $file, string $name, OutputInterface $output): void
    {
        $message = sprintf(
            "<info>Exported %d item(s) to '%s' via the %s exporter</info>",
            count($allItems),
            $file,
            $name
        );
        $output->writeln($message);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message
     * @param string $mode
     */
    private function listExporters(
        InputInterface $input,
        OutputInterface $output,
        string $message,
        string $mode
    ): void {
        $output->writeln($message);
        $output->writeln('<info>Available exporters and formats:</info>');
        $all = array_keys($this->exporterRegistry->all());
        $exporters = [];
        // "sylius.country.csv" is an example of an exporter
        foreach ($all as $exporter) {
            $exporter = explode('.', $exporter);
            // saves the exporter in the exporters array, sets the exporterentity as the first key of the 2d array and the exportertypes each in the second array
            $exporters[$exporter[1]][] = $exporter[2];
        }

        $list = [];
        foreach ($exporters as $exporter => $formats) {
            // prints the exporterentity, implodes the types and outputs them in a form
            if ($mode === '1') {
                $list[] = sprintf(
                    '%s (formats: %s)',
                    $exporter,
                    implode(', ', $formats)
                );
            } else {
                $list[] = sprintf(
                    '%s',
                    $exporter
                );
            }
        }

        $io = new SymfonyStyle($input, $output);
        $io->listing($list);
        exit(0);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message
     * @param array $modes
     */
    private function listMethods(
        InputInterface $input,
        OutputInterface $output,
        string $message,
        array $modes
    ): void {
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
