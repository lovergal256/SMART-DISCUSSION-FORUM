<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CollaborativeFilteringService
{
    public function recommendGroups(int $userId, int $limit = 4): array
    {
        $memberships = DB::table('group_members')
            ->select('GroupID', 'UserID')
            ->get()
            ->groupBy('GroupID')
            ->map(fn ($rows) => $rows->pluck('UserID')->all());

        $userGroupIds = DB::table('group_members')
            ->where('UserID', $userId)
            ->pluck('GroupID')
            ->all();

        if (empty($userGroupIds)) {
            return [];
        }

        $scores = [];

        foreach ($memberships as $candidateGroupId => $candidateMembers) {
            if (in_array($candidateGroupId, $userGroupIds)) {
                continue;
            }

            $totalSimilarity = 0;

            foreach ($userGroupIds as $sourceGroupId) {
                $sourceMembers = $memberships[$sourceGroupId] ?? [];
                $totalSimilarity += $this->cosineSimilarity($sourceMembers, $candidateMembers);
            }

            if ($totalSimilarity > 0) {
                $scores[$candidateGroupId] = $totalSimilarity;
            }
        }

        arsort($scores);

        return array_slice(array_keys($scores), 0, $limit);
    }

    private function cosineSimilarity(array $membersA, array $membersB): float
    {
        if (empty($membersA) || empty($membersB)) {
            return 0.0;
        }

        $setA = array_flip($membersA);
        $setB = array_flip($membersB);
        $intersection = count(array_intersect_key($setA, $setB));

        $magnitude = sqrt(count($membersA) * count($membersB));

        return $magnitude > 0 ? $intersection / $magnitude : 0.0;
    }
}