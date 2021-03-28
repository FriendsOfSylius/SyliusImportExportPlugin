<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ImportDataCommand extends Command
{
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
            ->setName('sylius:import')
            ->setDescription('Import a file.')
            ->setDefinition([
                new InputArgument('importer', InputArgument::OPTIONAL, 'The importer to use.'),
                new InputArgument('file', InputArgument::OPTIONAL, 'The file to import.'),
                new InputOption('format', null, InputOption::VALUE_OPTIONAL, 'The format of the file to import'),
                new InputOption(
                    'details',
                    null,
                    InputOption::VALUE_NONE,
                    'If to return details about skipped/failed rows'
                ),
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

        $file = $input->getArgument('file');
        $format = $input->getOption('format');

        if (null === $format) {
            try {
                $info = new SplFileInfo($file);
                $format = $info->getExtension();
                $message = sprintf(
                    '<info>The %s format has been detected.</info>',
                    $format
                );
                $output->writeln($message);
            } catch (\Throwable $exception) {
                $output->writeln("<error>Format can't be detected.</error>");

                return 0;
            } finally {
                $output->writeln('You can set it manually by using --format parameter');
            }
        }

        if (!\is_string($format)) {
            return 0;
        }

        $name = ImporterRegistry::buildServiceName($importer, $format);
        if (!$this->importerRegistry->has($name)) {
            $this->listImporters($input, $output, sprintf('There is no \'%s\' importer.', $name));
        }

        /** @var ImporterInterface $service */
        $service = $this->importerRegistry->get($name);
        $result = $service->import($file);

        $message = sprintf(
            "<info>Imported '%s' via the %s importer</info>",
            $file,
            $name
        );
        $output->writeln($message);

        $io = new SymfonyStyle($input, $output);

        $details = $input->getOption('details');
        if ('' !== $details) {
            $imported = implode(', ', $result->getSuccessRows());
            $skipped = implode(', ', $result->getSkippedRows());
            $failed = implode(', ', $result->getFailedRows());
            $countOrRows = 'rows';
        } else {
            $imported = count($result->getSuccessRows());
            $skipped = count($result->getSkippedRows());
            $failed = count($result->getFailedRows());
            $countOrRows = 'count';
        }

        $io->listing(
            [
                sprintf('Time taken: %s ms ', $result->getDuration()),
                sprintf('Imported %s: %s', $countOrRows, $imported),
                sprintf('Skipped %s: %s', $countOrRows, $skipped),
                sprintf('Failed %s: %s', $countOrRows, $failed),
            ]
        );

        return 0;
    }

    private function listImporters(InputInterface $input, OutputInterface $output, ?string $errorMessage = null): void
    {
        $output->writeln('<info>Available importers:</info>');
        $all = array_keys($this->importerRegistry->all());
        $importers = [];
        foreach ($all as $importer) {
            $importer = explode('.', $importer);
            $format = \array_pop($importer);
            $type = \implode('.', $importer);

            $importers[$type][] = $format;
        }

        $list = [];
        foreach ($importers as $importer => $formats) {
            $list[] = sprintf(
                '%s (formats: %s)',
                $importer,
                implode(', ', $formats)
            );
        }

        $io = new SymfonyStyle($input, $output);
        $io->listing($list);

        if (null !== $errorMessage) {
            throw new \RuntimeException($errorMessage);
        }
    }
}
