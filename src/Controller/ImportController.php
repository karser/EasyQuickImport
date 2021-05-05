<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Import;
use App\Exception\AppException;
use App\Exception\ValidationsException;
use App\Form\Import\CreateImportFlow;
use App\SheetScheduler;
use App\SheetScheduler\SampleSheetGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class ImportController extends AbstractController
{
    public function create(CreateImportFlow $flow, SheetScheduler $sheetScheduler): Response
    {
        $import = new Import();

        $flow->bind($import);

        // form of the current step
        $form = $flow->createForm();

        $entities = [];
        $errors = [];
        try {
            if ($flow->isValid($form)) {
                $flow->saveCurrentStepData($form);

                if ($flow->getCurrentStepLabel() === 'import_wizard_step.mapping') {
                    Assert::notNull($file = $import->getFile());
                    $items = $sheetScheduler->loadFile($file, $import->getFieldsMapping());
                    Assert::notNull($type = $import->getImportType());
                    Assert::notNull($company = $import->getCompany());

                    $entities = $sheetScheduler->denormalize($type, $items);
                    Assert::notNull($dateFormat = $import->getDateFormat());
                    $entities = $sheetScheduler->canonizeDate($entities, $dateFormat);
                    $sheetScheduler->validateAllEntities($company, $entities);
                }

                if ($flow->nextStep()) {
                    // form for the next step
                    $form = $flow->createForm();
                } else {
                    // flow finished
                    $company = $import->getCompany();
                    Assert::notNull($company);
                    $type = $import->getImportType();
                    Assert::notNull($type);
                    $file = $import->getFile();
                    Assert::notNull($file);

                    $scheduled = $sheetScheduler->schedule($company, $type, $file, $import->getFieldsMapping(), $import->getDateFormat());
                    $this->addFlash('success', "{$scheduled} record(s) have been scheduled");
                    $flow->reset();

                    return $this->redirect($this->generateUrl('app_import_scheduled'));
                }
            }
        } catch (ValidationsException $e) {
            $errors = array_map(fn (AppException $nE) => nl2br($nE->getMessage()), $e->getExceptions());
        } catch (AppException $e) {
            $errors = [nl2br($e->getMessage())];
        }

        return $this->render('import/create.html.twig', [
            'form' => $form->createView(),
            'flow' => $flow,
            'entities' => $entities,
            'errors' => array_values(array_unique($errors)),
        ]);
    }

    public function scheduled(): Response
    {
        return $this->render('import/scheduled.html.twig', []);
    }

    public function sample(SampleSheetGenerator $sampleSheetGenerator, string $type): Response
    {
        $content = $sampleSheetGenerator->generateSampleCsv($type);
        return $this->downloadFileResponse($type.'-sample.csv', $content);
    }

    private function downloadFileResponse(string $filename, string $content): Response
    {
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-length', (string)strlen($content));
        $response->setContent($content);

        return $response;
    }
}
