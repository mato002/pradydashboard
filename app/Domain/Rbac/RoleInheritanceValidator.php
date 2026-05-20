<?php

namespace App\Domain\Rbac;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoleInheritanceValidator
{
    public function assertCanInherit(Role $parent, Role $child): void
    {
        if ($parent->id === $child->id) {
            throw new InvalidArgumentException(__('A role cannot inherit from itself.'));
        }

        if ($this->wouldCreateCycle($parent->id, $child->id)) {
            throw new InvalidArgumentException(__('Circular role inheritance is not allowed.'));
        }
    }

    public function wouldCreateCycle(int $parentId, int $childId): bool
    {
        $ancestorsOfParent = $this->ancestorIds($parentId);
        $ancestorsOfParent[] = $parentId;

        if (in_array($childId, $ancestorsOfParent, true)) {
            return true;
        }

        return in_array($parentId, $this->descendantIds($childId), true);
    }

    /** @return list<int> */
    private function ancestorIds(int $roleId): array
    {
        $ids = [];
        $queue = DB::table('role_inheritance')
            ->where('child_role_id', $roleId)
            ->pluck('parent_role_id')
            ->all();

        while ($queue) {
            $current = array_shift($queue);
            if (in_array($current, $ids, true)) {
                continue;
            }
            $ids[] = $current;
            $parents = DB::table('role_inheritance')
                ->where('child_role_id', $current)
                ->pluck('parent_role_id')
                ->all();
            $queue = array_merge($queue, $parents);
        }

        return $ids;
    }

    /** @return list<int> */
    private function descendantIds(int $roleId): array
    {
        $ids = [];
        $queue = DB::table('role_inheritance')
            ->where('parent_role_id', $roleId)
            ->pluck('child_role_id')
            ->all();

        while ($queue) {
            $current = array_shift($queue);
            if (in_array($current, $ids, true)) {
                continue;
            }
            $ids[] = $current;
            $children = DB::table('role_inheritance')
                ->where('parent_role_id', $current)
                ->pluck('child_role_id')
                ->all();
            $queue = array_merge($queue, $children);
        }

        return $ids;
    }
}
