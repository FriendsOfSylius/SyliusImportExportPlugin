<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
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
                new InputOption('format', null, InputOption::VALUE_REQUIRED, 'The format of the file to export to'),
                /** @todo Extracting details to show with this option. At the moment it will have no effect */
                new InputOption('details', null, InputOption::VALUE_NONE,
                    'If to return details about skipped/failed rows'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var string $exporter */
        $exporter = $input->getArgument('exporter');

        if (empty($exporter)) {
            $this->listExporters($input, $output);
        }
        $format = $input->getOption('format');
        $name = ExporterRegistry::buildServiceName('sylius.' . $exporter, $format);

        if (!$this->exporterRegistry->has($name)) {
            $this->listExporters($input, $output, sprintf('There is no \'%s\' exporter.', $name));
        }

        $file = $input->getArgument('file');

        /** @var RepositoryInterface $repository */
        $repository = $this->container->get('sylius.repository.' . $exporter);
        $items = $repository->findAll();
        $idsToExport = array_map(function (ResourceInterface $item) {
            return $item->getId();
        }, $items);

        /** @var ResourceExporterInterface $service */
        $service = $this->exporterRegistry->get($name);
        $service->setExportFile($file);

        $service->export($idsToExport);

        $service->finish();

        $output->writeln(sprintf(
          "<info>Exported %d item(s) to '%s' via the %s exporter</info>",
          count($items),
          $file,
          $name
        ));
    }

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
            // prints the exporterentity, implodes the types and outputs them in a form
            $list[] = sprintf(
                '%s (formats: %s)',
                $exporter,
                implode(', ', $formats)
            );
        }

        $io = new SymfonyStyle($input, $output);
        $io->listing($list);

        if ($errorMessage) {
            throw new \RuntimeException($errorMessage);
        }
    }
}
