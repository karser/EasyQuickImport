<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\QuickbooksCompany;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConvertTransactionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', EntityType::class, [
                'class' => QuickbooksCompany::class,
                'choice_label' => 'companyName',
                'choice_value' => 'qbUsername',
                'placeholder' => 'select option',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('local_file', FileType::class, [
                'label' => 'Upload file',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10m',
                        'mimeTypes' => [
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid CSV document',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Convert'])
        ;
    }
}
