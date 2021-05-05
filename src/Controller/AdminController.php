<?php declare(strict_types=1);

namespace App\Controller;

use App\Currency\CurrencyMap;
use App\Entity\QuickbooksCompany;
use App\QuickbooksServer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class AdminController extends EasyAdminController
{
    private QuickbooksServer $server;

    /** @var EntityManagerInterface */
    protected $em;

    public function __construct(QuickbooksServer $server, EntityManagerInterface $em)
    {
        $this->server = $server;
        $this->em = $em;
    }

    public function createQuickbooksCompanyEntityFormBuilder(object $entity, string $view): FormBuilderInterface
    {
        $formBuilder = $this->createEntityFormBuilder($entity, $view);
        $fields = $formBuilder->all();
        foreach ($fields as $fieldId => $field) {
            if ($fieldId === 'baseCurrency') {
                $map = new CurrencyMap();

                $formBuilder->add($fieldId, ChoiceType::class, [
                    'placeholder' => 'select option',
                    'required' => false,
                    'choices' => $map->getFormChoices(),
                ]);
            }
        }

        return $formBuilder;
    }

    public function downloadQbwcConfigAction(?Request $request = null): Response
    {
        Assert::notNull($request = $this->request ?? $request);
        $qbUsername = $request->query->get('id');
        $entity = $this->em->getRepository(QuickbooksCompany::class)->findOneBy(['qbUsername' => $qbUsername]);
        Assert::isInstanceOf($entity, QuickbooksCompany::class, "Company with id {$qbUsername} not found");

        return new Response($this->server->config($entity), 200, [
            'Content-type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="'.$qbUsername.'.qwc"',
        ]);
    }

    public function syncAccountsAction(?Request $request = null): Response
    {
        Assert::notNull($request = $this->request ?? $request);
        $qbUsername = $request->query->get('id');
        return $this->redirectToRoute('app_schedule_accounts', ['qbUsername' => $qbUsername]);
    }
}
