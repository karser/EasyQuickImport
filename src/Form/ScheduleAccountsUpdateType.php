<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\QuickbooksCompany;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ScheduleAccountsUpdateType extends AbstractType
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
            ->add('submit', SubmitType::class, ['label' => 'Schedule'])
        ;
    }
}
