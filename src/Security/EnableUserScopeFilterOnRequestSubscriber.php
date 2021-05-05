<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\QuickbooksCompany;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class EnableUserScopeFilterOnRequestSubscriber implements EventSubscriberInterface
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (null !== $this->security && ($user = $this->security->getUser()) instanceof User) {
            /** @var UserScopeFilter $filter */
            $filter = $this->em
                ->getFilters()
                ->enable('user_scope');

            $filter->setParameter('userId', (string)$user->getId());
            $qbUsernames = array_filter(array_map(fn(QuickbooksCompany $company) => $company->getQbUsername(), iterator_to_array($user->getCompanies())));
            $filter->setParameter('qbUsernames', implode('|', $qbUsernames));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
