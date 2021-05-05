<?php declare(strict_types=1);

namespace App\Form\Import;

use App\Entity\Import;
use App\Entity\QuickbooksCompany;
use App\SheetScheduler;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Webmozart\Assert\Assert;

class ImportUploadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', EntityType::class, [
                'class' => QuickbooksCompany::class,
                'choice_label' => 'companyName',
                'choice_value' => 'qbUsername',
                'placeholder' => 'select option',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('importType', ChoiceType::class, [
                'placeholder' => 'select option',
                'choices' => [
                    SheetScheduler::TYPE_CUSTOMER_INVOICE => SheetScheduler::TYPE_CUSTOMER_INVOICE,
                    SheetScheduler::TYPE_CUSTOMER => SheetScheduler::TYPE_CUSTOMER,
                    SheetScheduler::TYPE_VENDOR_BILL => SheetScheduler::TYPE_VENDOR_BILL,
                    SheetScheduler::TYPE_VENDOR => SheetScheduler::TYPE_VENDOR,
                    SheetScheduler::TYPE_TRANSACTION => SheetScheduler::TYPE_TRANSACTION,
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'Upload file',
                'constraints' => [
                    new File([
                        'maxSize' => '100m',
                        'mimeTypes' => [
                            'text/plain',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.openxmlformatsofficedocument.spreadsheetml.sheet',
                            'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid CSV/XLS/XLSX document',
                    ]),
                    new NotBlank(),
                ],
            ])
            ->add('dateFormat', ChoiceType::class, [
                'placeholder' => 'select option',
                'choices' => $this->getDateFormatChoices(),
                'constraints' => [
                    new NotBlank(),
                ],
                'data' => 'Y-m-d',
            ])
        ;
    }

    private function getDateFormatChoices(): array
    {
        $formats = [
            'Y-m-d', // yyyy-MM-dd
            'Y.m.d',
            'M j, Y',
            'n/j/Y', // M/d/yyyy
            'n/j/y', // M/d/yy
            'm/d/y', // MM/dd/yy
            'm/d/Y', // MM/dd/yyyy
            'y/m/d', // yy/MM/dd
            'd-M-y', // dd-MMM-yy
            'Y/m/d',
            'd/m/y',
        ];
        $date = new \DateTime('1999-03-31');
        $choices = array_combine(array_map(
            fn(string $format) => $date->format($format).' ('.$format.')',
        $formats), $formats);
        Assert::isArray($choices);
        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Import::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'importUpload';
    }
}
