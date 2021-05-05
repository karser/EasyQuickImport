<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Entity\QuickbooksCompany;
use Symfony\Contracts\EventDispatcher\Event;

class EntityOnScheduledEvent extends Event
{
    public const PRIORITY_MANUPILATE = 20;
    public const PRIORITY_UPDATE = 10;
    public const PRIORITY_POST_UPDATE = 5;

    private $user;
    private $entities;
    private $line;

    public function __construct(QuickbooksCompany $user, array $entities, int $line)
    {
        $this->user = $user;
        $this->entities = $entities;
        $this->line = $line;
    }

    /**
     * @return QuickbooksCompany
     */
    public function getUser(): QuickbooksCompany
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return array
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @param array $entities
     */
    public function setEntities(array $entities): void
    {
        $this->entities = $entities;
    }
}
