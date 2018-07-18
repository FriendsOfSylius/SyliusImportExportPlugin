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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class ExportDataToMessageQueueCommand extends Command
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
            ->setName('sylius:export-to-message-queue')
            ->setDescription('Export data to message queue')
            ->setDefinition([
                new InputArgument('exporter', InputArgument::OPTIONAL, 'The exporter to uses.'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exporter = $input->getArgument('exporter');

        if (empty($exporter)) {
            $this->listExporters($input, $output);

            return;
        }

        /** @var RepositoryInterface $repository */
        $repository = $this->container->get('sylius.repository.' . $exporter);
        $allItems = $repository->findAll();

        /** @var array $idsToExport */
        $idsToExport = $this->prepareExport($allItems);

        $name = ExporterRegistry::buildServiceName('sylius.' . $exporter, 'json');

        if (!$this->exporterRegistry->has($name)) {
            $this->listExporters($input, $output, sprintf('There is no \'%s\' exporter.', $name));
        }

        $this->export($name, $idsToExport, $exporter);
        $this->finishExport($allItems, 'message queue', $name, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param null|string $errorMessage
     */
    private function listExporters(InputInterface $input, OutputInterface $output, ?string $errorMessage = null): void
    {
        $output->writeln('<info>Available exporters:</info>');
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
            $list[] = sprintf(
                '%s',
                $exporter
            );
        }

        $io = new SymfonyStyle($input, $output);
        $io->listing($list);

        if ($errorMessage) {
            throw new \RuntimeException($errorMessage);
        }
    }

    /**
     * @param array $allItems
     *
     * @return array
     */
    private function prepareExport(array $allItems): array
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
     * @param array $idsToExport
     * @param string $exporter
     */
    private function export(string $name, array $idsToExport, string $exporter): void
    {
        /** @var ResourceExporterInterface $service */
        $service = $this->exporterRegistry->get($name);
        $service->export($idsToExport);
        $itemsToExport = $service->getExportedData();

        $mqItemWriter = new MqItemWriter(new RedisConnectionFactory());
        $mqItemWriter->initQueue('sylius.export.queue.' . $exporter);
        $mqItemWriter->write(json_decode($itemsToExport));
    }

    /**
     * @param array $allItems
     * @param string $file
     * @param string $name
     * @param OutputInterface $output
     */
    private function finishExport(array $allItems, string $file, string $name, OutputInterface $output): void
    {
        $message = sprintf(
            "<info>Exported %d item(s) to '%s' via the %s exporter</info>",
            count($allItems),
            $file,
            $name
        );
        $output->writeln($message);
    }
}
