<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Entity\QuickbooksCompany;

interface EntityTransformerInterface
{
    public function supports(string $class): bool;

    /**
     * Transforms a local entity to QB one
     * @param object $entity
     */
    public function transform($entity): array;

    public function setCompany(?QuickbooksCompany $company): void;
}
