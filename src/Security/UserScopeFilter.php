<?php declare(strict_types=1);

namespace App\Security;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class UserScopeFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->hasAssociation('user') && $this->hasParameter('userId')) {
            $userId = $this->getParameter('userId');
            return "{$targetTableAlias}.user_id = {$userId}";
        }
        if ($targetEntity->hasAssociation('company') && $this->hasParameter('qbUsernames')) {
            $sql = array_map(fn(string $qbUsername) => "{$targetTableAlias}.qb_username = '{$qbUsername}'", $this->getQbUsernames());
            return empty($sql) ? 'false' : implode(' OR ', $sql);
        }

        return '';
    }

    private function getQbUsernames(): array
    {
        $qbUsernames = $this->getParameter('qbUsernames');
        $qbUsernames = str_replace("'", '', $qbUsernames);
        $qbUsernames = explode('|', $qbUsernames);
        $qbUsernames = array_values((array)array_filter($qbUsernames));

        return $qbUsernames;
    }
}
