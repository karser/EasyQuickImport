<?php declare(strict_types=1);

namespace App\EasyAdmin;

use App\Entity\QuickbooksCompany;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Security;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function setDefaultsToCompany(GenericEvent $event): void
    {
        $entity = $event->getSubject();

        if ($entity instanceof QuickbooksCompany && ($user = $this->security->getUser()) instanceof User) {
            $entity->setUser($user);
            $event['entity'] = $entity;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'easy_admin.pre_persist' => ['setDefaultsToCompany'],
        ];
    }
}
