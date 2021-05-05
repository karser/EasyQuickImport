<?php declare(strict_types=1);

namespace App\Form\Import;

use App\Entity\Import;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

class MapFieldsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Import|null $import */
        $import = $options['data'] ?? null;
        Assert::isInstanceOf($import, Import::class);

        $builder->add('fieldsMapping', SimpleMappingFormType::class, [
            'import' => $import,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Import::class,
        ]);
    }
}
