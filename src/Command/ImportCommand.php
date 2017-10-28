<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Command;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use Sylius\Component\Registry\ServiceRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ImportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('sylius:import')
            ->setDescription('Import a csv file.')
            ->setDefinition([
                new InputArgument('importer', InputArgument::OPTIONAL, 'The importer to use.'),
                new InputArgument('file', InputArgument::OPTIONAL, 'The csv file to import.'),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ServiceRegistry $registry */
        $registry = $this->getContainer()->get('sylius.importers_registry');

        $importType = $input->getArgument('importer');
        if (empty($importType)) {
            $output->writeln('<info>Available importers:</info>');

            $io = new SymfonyStyle($input, $output);
            $io->listing(array_keys($registry->all()));

            return;
        }

        $file = $input->getArgument('file');

        if (!$registry->has($importType)) {
            $message = sprintf(
                "<error>There is no '%s' importer. Available importers: %s</error>",
                $importType,
                implode(', ', array_keys($registry->all()))
            );
            $output->writeln($message);

            return 1;
        }

        /** @var ImporterInterface $service */
        $service = $registry->get($importType);
        $service->import($file);

        $message = sprintf(
            "<info>Successfully imported '%s' via the %s importer</info>",
            $file,
            $importType
        );

        $output->writeln($message);
    }
}
