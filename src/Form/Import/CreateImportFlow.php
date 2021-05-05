<?php declare(strict_types=1);

namespace App\Form\Import;

use Craue\FormFlowBundle\Form\FormFlow;

class CreateImportFlow extends FormFlow
{
    protected function loadStepsConfig()
    {
        return [
            [
                'label' => 'import_wizard_step.upload',
                'form_type' => ImportUploadFormType::class,
            ],
            [
                'label' => 'import_wizard_step.mapping',
                'form_type' => MapFieldsForm::class,
            ],
            [
                'label' => 'import_wizard_step.confirmation',
            ],
        ];
    }
}
