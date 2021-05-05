<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\QuickbooksCompany;
use App\SheetScheduler;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class ScheduleType extends AbstractType
{
    /** @var FilesystemInterface&Filesystem */
    private $sheetFilesystem;

    /**
     * @param FilesystemInterface&Filesystem $sheetFilesystem
     */
    public function __construct(FilesystemInterface $sheetFilesystem)
    {
        $this->sheetFilesystem = $sheetFilesystem;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $files = array_filter($this->sheetFilesystem->listFiles(), function($file): bool {
            return preg_match('/\.(xls|csv)$/', $file['path']) === 1;
        });

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
            ->add('type', ChoiceType::class, [
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
            ->add('remote_file', ChoiceType::class, [
                'placeholder' => 'select option',
                'required' => false,
                'choices' => array_column($files, 'path', 'path'),
                'constraints' => [
//                    new NotBlank(),
                ],
            ])
            ->add('local_file', FileType::class, [
                'label' => 'Upload file',
                'required' => false,
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
                    ])
                ],
            ])
            ->add('dry_run', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Schedule'])
        ;
    }
}
