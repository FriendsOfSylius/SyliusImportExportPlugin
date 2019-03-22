<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Form;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    /** @var ImporterRegistry */
    private $importerRegistry;

    public function __construct(ImporterRegistry $importerRegistry)
    {
        $this->importerRegistry = $importerRegistry;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('importer_type');
        $resolver->setAllowedTypes('importer_type', 'string');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('format', ChoiceType::class, [
                'choices' => $this->buildChoices($options),
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ])
            ->add('import-data', FileType::class, ['required' => true])
        ;
    }

    private function buildChoices(array $options): array
    {
        /** @var string $importerType */
        $importerType = $options['importer_type'];

        $choices = [];
        if ($this->importerRegistry->has(ImporterRegistry::buildServiceName($importerType, 'csv'))) {
            $choices['CSV'] = 'csv';
        }
        if ($this->importerRegistry->has(ImporterRegistry::buildServiceName($importerType, 'xlsx'))) {
            $choices['Excel'] = 'xlsx';
        }
        $choices['JSON'] = 'json';

        return $choices;
    }
}
