<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ExportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('format', ChoiceType::class, [
                'choices' => [
                    'CSV' => 'csv',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ])
            ->add('export-file-name', null, ['required' => true])
        ;
    }
}
