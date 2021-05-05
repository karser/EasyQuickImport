<?php declare(strict_types=1);

namespace App\Form;

use App\SheetScheduler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DownloadSampleSheetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('submit', SubmitType::class, ['label' => 'Download'])
        ;
    }
}
