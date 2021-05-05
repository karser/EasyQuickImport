<?php declare(strict_types=1);

namespace App\Accounts;

use App\Event\QuickbooksServerResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateAccountsSubscriber implements EventSubscriberInterface
{
    private $accountsUpdater;

    public function __construct(AccountsUpdater $accountsUpdater)
    {
        $this->accountsUpdater = $accountsUpdater;
    }

    public function onResponse(QuickbooksServerResponseEvent $event): void
    {
        if ($event->getIdent() === AccountsUpdater::ACCOUNTS_UPDATE_REQUEST_ID) {
            $this->accountsUpdater->update($event->getUser(), $event->getXml());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            QuickbooksServerResponseEvent::class => 'onResponse',
        ];
    }
}
