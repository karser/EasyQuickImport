<?php declare(strict_types=1);

namespace App\Twig;

use App\Entity\QuickbooksAccount;
use App\Entity\QuickbooksCompany;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Webmozart\Assert\Assert;

class TwigExtension extends AbstractExtension
{
    private $em;
    private $twig;
    private $normalizer;

    public function __construct(EntityManagerInterface $em, Environment $twig, NormalizerInterface $normalizer)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->normalizer = $normalizer;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('alerts', [$this, 'getAlerts']),
            new TwigFunction('render_preview', [$this, 'renderPreview']),
        ];
    }

    public function getAlerts(): string
    {
        $accountRepo = $this->em->getRepository(QuickbooksAccount::class);
        $companyRepo = $this->em->getRepository(QuickbooksCompany::class);
        $companies = $companyRepo->findBy([]);
        $accountlessCompanies = array_values(array_filter($companies, static function(QuickbooksCompany $company) use ($accountRepo): bool  {
            return $accountRepo->findOneBy(['company' => $company]) === null;
        }));
        $noCompanies =  count($companies) === 0;

        return $this->twig->render('helper/alerts.html.twig', [
            'has_alerts' => $noCompanies || count($accountlessCompanies) > 0,
            'no_companies' => $noCompanies,
            'accountless_companies' => $accountlessCompanies,
        ]);
    }

    public function renderPreview(array $entities): string
    {
        $entities = $this->normalizer->normalize($entities);
        Assert::isArray($entities);
        $entities = $this->removeEmptyElements($entities);
        return $this->twig->render('helper/render_preview.html.twig', [
            'entities' => $entities,
        ]);
    }

    private function removeEmptyElements(array $haystack): array
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = $this->removeEmptyElements($haystack[$key]);
            }
            if (in_array($haystack[$key], [null, ''], true)) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }
}
