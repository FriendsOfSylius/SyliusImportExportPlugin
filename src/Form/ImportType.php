<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('format', ChoiceType::class, [
                'choices' => [
                    'CSV' => 'csv',
                    'Excel' => 'xlsx',
                    'JSON' => 'json',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ])
            ->add('import-data', FileType::class, ['required' => true])
        ;
    }
}
