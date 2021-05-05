<?php declare(strict_types=1);

namespace App;

use App\Entity\QuickbooksCompany;

interface QuickbooksServerInterface
{
    public function config(QuickbooksCompany $user): string;

    public function schedule(?string $username, string $action, string $id, ?string $qbxml = null, ?array $extra = null): bool;

    public function qbwc(?string $input): string;

    public function truncateQueue(): void;
}
