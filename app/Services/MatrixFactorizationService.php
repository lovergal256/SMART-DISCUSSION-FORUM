<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MatrixFactorizationService
{
    private int $factors;
    private float $learningRate;
    private float $regularization;
    private int $epochs;

    public array $lossHistory = []; // tracks training progress, epoch by epoch

    public function __construct(int $factors = 5, float $learningRate = 0.05, float $regularization = 0.02, int $epochs = 300)
    {
        $this->factors = $factors;
        $this->learningRate = $learningRate;
        $this->regularization = $regularization;
        $this->epochs = $epochs;
    }

    public function recommendGroups(int $userId, int $limit = 4): array
    {
        $memberships = DB::table('group_members')->select('UserID', 'GroupID')->get();

        $userIds = $memberships->pluck('UserID')->unique()->values();
        $groupIds = $memberships->pluck('GroupID')->unique()->values();

        // Need enough data and history for this user to train meaningfully
        if ($userIds->count() < 2 || $groupIds->count() < 2 || !$userIds->contains($userId)) {
            return [];
        }

        $userIndex = array_flip($userIds->all());
        $groupIndex = array_flip($groupIds->all());

        $numUsers = $userIds->count();
        $numGroups = $groupIds->count();

        $interactions = $memberships->map(function ($row) use ($userIndex, $groupIndex) {
            return [$userIndex[$row->UserID], $groupIndex[$row->GroupID], 1.0];
        })->all();

        $P = $this->randomMatrix($numUsers, $this->factors);  // user latent factors
        $Q = $this->randomMatrix($numGroups, $this->factors); // group latent factors

        $this->lossHistory = [];

        // --- TRAINING LOOP (this is the actual "training a model" part) ---
        for ($epoch = 0; $epoch < $this->epochs; $epoch++) {
            $totalError = 0.0;

            foreach ($interactions as [$u, $g, $actual]) {
                $prediction = $this->dot($P[$u], $Q[$g]);
                $error = $actual - $prediction;
                $totalError += $error ** 2;

                for ($f = 0; $f < $this->factors; $f++) {
                    $pVal = $P[$u][$f];
                    $qVal = $Q[$g][$f];

                    $P[$u][$f] += $this->learningRate * (2 * $error * $qVal - $this->regularization * $pVal);
                    $Q[$g][$f] += $this->learningRate * (2 * $error * $pVal - $this->regularization * $qVal);
                }
            }

            $this->lossHistory[] = round($totalError / count($interactions), 5);
        }

        // --- PREDICTION (using the now-trained vectors) ---
        $uIdx = $userIndex[$userId];
        $alreadyJoined = $memberships->where('UserID', $userId)->pluck('GroupID')->all();

        $scores = [];
        foreach ($groupIds as $groupId) {
            if (in_array($groupId, $alreadyJoined)) {
                continue;
            }
            $gIdx = $groupIndex[$groupId];
            $scores[$groupId] = $this->dot($P[$uIdx], $Q[$gIdx]);
        }

        arsort($scores);

        return array_slice(array_keys($scores), 0, $limit);
    }

    private function randomMatrix(int $rows, int $cols): array
    {
        $matrix = [];
        for ($i = 0; $i < $rows; $i++) {
            $row = [];
            for ($j = 0; $j < $cols; $j++) {
                $row[] = (mt_rand() / mt_getrandmax()) * 0.1;
            }
            $matrix[] = $row;
        }
        return $matrix;
    }

    private function dot(array $a, array $b): float
    {
        $sum = 0.0;
        foreach ($a as $i => $v) {
            $sum += $v * $b[$i];
        }
        return $sum;
    }
}