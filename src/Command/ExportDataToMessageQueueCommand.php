<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ItemWriterInterface;
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

    /** @var ExporterRegistry */
    private $exporterRegistry;

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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $exporter */
        $exporter = $input->getArgument('exporter');

        if ('' === $exporter) {
            $this->listExporters($input, $output);

            return 0;
        }

        $domain = 'sylius';
        // backward compatibility with the old configuration
        if (count(\explode('.', $exporter)) === 2) {
            [$domain, $exporter] = \explode('.', $exporter);
        }

        /** @var RepositoryInterface $repository */
        $repository = $this->container->get($domain . '.repository.' . $exporter);

        /** @var ResourceInterface[] $items */
        $items = $repository->findAll();

        /** @var array $idsToExport */
        $idsToExport = $this->prepareExport($items);

        $name = ExporterRegistry::buildServiceName($domain . '.' . $exporter, 'json');

        if (!$this->exporterRegistry->has($name)) {
            $this->listExporters($input, $output, sprintf('There is no \'%s\' exporter.', $name));
        }

        /** @var ItemWriterInterface $mqItemWriter */
        $mqItemWriter = $this->container->get('sylius.message_queue_writer');
        $this->export($mqItemWriter, $name, $idsToExport, $exporter);
        $this->finishExport($items, 'message queue', $name, $output);

        return 0;
    }

    private function listExporters(InputInterface $input, OutputInterface $output, ?string $errorMessage = null): void
    {
        $output->writeln('<info>Available exporters:</info>');
        $all = array_keys($this->exporterRegistry->all());
        $exporters = [];
        // "sylius.country.csv" is an example of an exporter
        foreach ($all as $exporter) {
            $exporter = explode('.', $exporter);
            $format = \array_pop($exporter);
            $type = \implode('.', $exporter);

            $exporters[$type][] = $format;
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

        if (null !== $errorMessage) {
            throw new \RuntimeException($errorMessage);
        }
    }

    /**
     * @param ResourceInterface[] $items
     *
     * @return int[]
     */
    private function prepareExport(array $items): array
    {
        return array_map(function (ResourceInterface $item) {
            return $item->getId();
        }, $items);
    }

    /**
     * @param int[] $idsToExport
     */
    private function export(ItemWriterInterface $mqItemWriter, string $name, array $idsToExport, string $exporter): void
    {
        /** @var ResourceExporterInterface $service */
        $service = $this->exporterRegistry->get($name);
        $service->export($idsToExport);
        $itemsToExport = $service->getExportedData();

        $mqItemWriter->initQueue('sylius.export.queue.' . $exporter);
        $mqItemWriter->write(json_decode($itemsToExport));
    }

    /**
     * @param ResourceInterface[] $items
     */
    private function finishExport(array $items, string $file, string $name, OutputInterface $output): void
    {
        $output->writeln(sprintf(
          "<info>Exported %d item(s) to '%s' via the %s exporter</info>",
          count($items),
          $file,
          $name
        ));
    }
}
