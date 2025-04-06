<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('csv', FileType::class, [
            'label' => 'Upload CSV file',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File([
                    'maxSize' => '4086k',
                    'mimeTypes' => [
                        'text/csv',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid CSV file',
                ])
            ],
        ])
        ->add('submit', SubmitType::class, ['label' => 'Upload CSV']);
    }
}
