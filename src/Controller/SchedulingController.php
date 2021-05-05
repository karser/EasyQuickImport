<?php declare(strict_types=1);

namespace App\Controller;

use App\Accounts\AccountsUpdater;
use App\Entity\QuickbooksCompany;
use App\Entity\QuickbooksCompanyRepositoryInterface;
use App\Exception\RuntimeException;
use App\Form\ConvertTransactionsType;
use App\Form\DownloadSampleSheetType;
use App\Form\ScheduleAccountsUpdateType;
use App\Form\ScheduleType;
use App\Form\TruncateQueueType;
use App\TransactionsConverter;
use App\QuickbooksFormatter;
use App\QuickbooksServer;
use App\SheetScheduler;
use App\SheetScheduler\SampleSheetGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class SchedulingController extends AbstractController
{
    private $server;
    private $em;

    public function __construct(QuickbooksServer $server, EntityManagerInterface $em)
    {
        $this->server = $server;
        $this->em = $em;
    }

    public function testSchedule(): Response
    {
//        $this->server->truncateQueue();
        $formatter = new QuickbooksFormatter();
        $this->server->schedule(null, QUICKBOOKS_QUERY_COMPANY, '100');
//        $this->server->schedule(null, QUICKBOOKS_ADD_CUSTOMER, '100', $formatter->getTestCustomerAdd());
//        $this->server->schedule(null, QUICKBOOKS_ADD_RECEIVE_PAYMENT, '100', $formatter->getTestPaymentReceiveAdd());
//        $this->server->schedule(null, QUICKBOOKS_ADD_INVOICE, '100', $formatter->getTestInvoiceAdd());
//        $this->server->schedule(null, QUICKBOOKS_ADD_VENDOR, '100', $formatter->getTestVendorAdd());
//        $this->server->schedule(null, QUICKBOOKS_ADD_BILL, '100', $formatter->getTestBillAdd());
//        $this->server->schedule(null, QUICKBOOKS_ADD_BILLPAYMENTCHECK, '100', $formatter->getTestBillPaymentCheckAdd());
//        $this->server->schedule(null, QUICKBOOKS_ADD_JOURNALENTRY, '100', $formatter->getTestJournalEntryAdd());

        $this->addFlash('success', 'The queue tables have been truncated');

        return $this->redirectToRoute('app_homepage');
    }

    public function schedule(Request $request, SheetScheduler $sheetScheduler,
                             TransactionsConverter $transactionsConverter,
                             SampleSheetGenerator $sampleSheetGenerator): Response
    {
        $truncateForm = $this->createForm(TruncateQueueType::class);
        $truncateForm->handleRequest($request);
        if ($truncateForm->isSubmitted() && $truncateForm->isValid()) {
            /** @var bool $confirm */
            $confirm = $truncateForm->get('confirm')->getData();
            if ($confirm) {
                $this->server->truncateQueue();
                $this->addFlash('success', 'The queue tables have been truncated');
                return $this->redirectToRoute('app_schedule');
            }

        }

        $scheduleForm = $this->createForm(ScheduleType::class);
        $scheduleForm->handleRequest($request);
        if ($scheduleForm->isSubmitted() && $scheduleForm->isValid()) {
            $remoteFile = $scheduleForm->get('remote_file')->getData();
            /** @var UploadedFile|null $localFile */
            $localFile = $scheduleForm->get('local_file')->getData();
            try {
                if ($remoteFile === null && $localFile === null) {
                    throw new RuntimeException('Either remote or local file must be submitted');
                }
                $file = $localFile ?? $sheetScheduler->copyToLocal($remoteFile);
                $type = $scheduleForm->get('type')->getData();
                /** @var QuickbooksCompany $user */
                $user = $scheduleForm->get('username')->getData();
                /** @var bool $dryRun */
                $dryRun = $scheduleForm->get('dry_run')->getData();
                if ($dryRun) {
                    $toSchedule = $sheetScheduler->dryRun($user, $type, $file);
                    return new Response($toSchedule, 200, [
                        'Content-Disposition' => 'attachment; filename="'.$type.'-dry-run.xml"',
                        'Content-type' => 'text/xml',
                    ]);
                }
                $scheduled = $sheetScheduler->schedule($user, $type, $file);
                $this->addFlash('success', "{$scheduled} record(s) have been scheduled");
                return $this->redirectToRoute('app_schedule');
            } catch (RuntimeException $e) {
                $this->addFlash('error', nl2br($e->getMessage()));
                return $this->redirectToRoute('app_schedule');
            }
        }

        $sampleForm = $this->createForm(DownloadSampleSheetType::class);
        $sampleForm->handleRequest($request);
        if ($sampleForm->isSubmitted() && $sampleForm->isValid()) {
            $type = $sampleForm->get('type')->getData();
            $content = $sampleSheetGenerator->generateSampleCsv($type);
            return $this->downloadFileResponse($type.'-sample.csv', $content);
        }

        $converterForm = $this->createForm(ConvertTransactionsType::class);
        $converterForm->handleRequest($request);
        if ($converterForm->isSubmitted() && $converterForm->isValid()) {
            try {
                $file = $converterForm->get('local_file')->getData();

                /** @var QuickbooksCompany $user */
                $user = $converterForm->get('username')->getData();
                $username = $user->getQbUsername();
                Assert::notNull($username);

                $content = $transactionsConverter->convertWrapper($file, $username);
                return $this->downloadFileResponse(time().'-transactions.csv', $content);
            } catch (\Exception $e) {
                $this->addFlash('error', nl2br($e->getMessage()));
                return $this->redirectToRoute('app_schedule');
            }
        }


        return $this->render('scheduling/schedule.html.twig', [
            'schedule_form' => $scheduleForm->createView(),
            'sample_form' => $sampleForm->createView(),
            'converter_form' => $converterForm->createView(),
            'truncate_form' => $truncateForm->createView(),
        ]);
    }

    public function scheduleAccounts(Request $request, AccountsUpdater $accountsUpdater,
                                     QuickbooksCompanyRepositoryInterface $companyRepo): Response
    {
        $company = null !== ($username = $request->query->get('qbUsername')) ? $companyRepo->find($username) : null;
        $scheduleAccountsUpdateForm = $this->createForm(ScheduleAccountsUpdateType::class, [
            'company' => $company,
        ]);
        $scheduleAccountsUpdateForm->handleRequest($request);
        if ($scheduleAccountsUpdateForm->isSubmitted() && $scheduleAccountsUpdateForm->isValid()) {
            /** @var QuickbooksCompany $user */
            $user = $scheduleAccountsUpdateForm->get('company')->getData();
            $qbUsername = $user->getQbUsername();
            Assert::notNull($qbUsername);
            if ($accountsUpdater->scheduleUpdate($qbUsername)) {
                $this->addFlash('success', sprintf('Accounts update has been scheduled. Now go to QuickBooks and run Web Connector. Then go back and check <a href="%s">Chart Of Accounts</a>',
                    $this->generateUrl('easyadmin', ['entity' => 'QuickbooksAccount', 'action' => 'list'])));
            }
            return $this->redirectToRoute('app_schedule_accounts', ['qbUsername' => $qbUsername]);
        }

        return $this->render('scheduling/accounts.html.twig', [
            'schedule_accounts_update_form' => $scheduleAccountsUpdateForm->createView(),
        ]);
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
