<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Form;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry;
use Port\Csv\CsvReaderFactory;
use Port\Excel\ExcelReaderFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    private const CLASS_CSV_READER = CsvReaderFactory::class;
    private const CLASS_EXCEL_READER = ExcelReaderFactory::class;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('importer_type');
        $resolver->setRequired('importer_registry');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var string $importerType */
        $importerType = $options['importer_type'];
        /** @var \FriendsOfSylius\SyliusImportExportPlugin\Exporter\ExporterRegistry $importerRegistry */
        $importerRegistry = $options['importer_registry'];

        $choices = [];
        $csvImporter = ExporterRegistry::buildServiceName($importerType, 'csv');
        if ($importerRegistry->has($csvImporter)) {
            $choices['CSV'] = 'csv';
        }
        $xlsxImporter = ExporterRegistry::buildServiceName($importerType, 'xlsx');
        if ($importerRegistry->has($xlsxImporter)) {
            $choices['Excel'] = 'xlsx';
        }
        $choices['JSON'] = 'json';
        $builder
            ->add('format', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ])
            ->add('import-data', FileType::class, ['required' => true])
        ;
    }
}
