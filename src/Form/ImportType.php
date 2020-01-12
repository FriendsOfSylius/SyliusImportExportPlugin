<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Form;

use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ImportType extends AbstractType
{
    /** @var ImporterRegistry */
    private $importerRegistry;

    public function __construct(ImporterRegistry $importerRegistry)
    {
        $this->importerRegistry = $importerRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', HiddenType::class, [
                'required' => true,
                'data' => $options['importer_type'],
                'constraints' => [
                    new EqualTo(['value' => $options['importer_type']]),
                ],
            ])
            ->add('format', ChoiceType::class, [
                'choices' => $this->buildChoices($options),
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('importer_type');
        $resolver->setAllowedTypes('importer_type', 'string');
        $resolver->setDefault('constraints', [
            new Callback([$this, 'validate']),
        ]);
    }

    public function validate($data, ExecutionContextInterface $context)
    {
        if ($context->getViolations()->count() > 0 || empty($data['type']) || empty($data['format'])) {
            return;
        }

        $name = ImporterRegistry::buildServiceName($data['type'], $data['format']);
        if (!$this->importerRegistry->has($name)) {
            $message = sprintf("No importer found of type '%s' for format '%s'", $data['type'], $data['format']);
            $context->addViolation($message);
        }
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
