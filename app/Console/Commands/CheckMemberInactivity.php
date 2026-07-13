<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Post;
use App\Models\Reply;
use App\Models\Warning;
use App\Models\Blacklist;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckMemberInactivity extends Command
{
    protected $signature = 'members:check-inactivity';
    protected $description = 'Warn and blacklist members who have not posted or replied for extended periods';

    public function handle()
    {
        $now = Carbon::now();

       $students = User::whereHas('roleRelation', function ($q) {
    $q->where('RoleName', 'student');
})->get();

        foreach ($students as $user) {
            // Skip anyone currently blacklisted (block is still active)
            $activeBlacklist = Blacklist::where('UserID', $user->UserID)
                ->where('EndDate', '>=', $now->toDateString())
                ->exists();

            if ($activeBlacklist) {
                continue;
            }

            $lastPost = Post::where('UserID', $user->UserID)->max('DatePosted');
            $lastReply = Reply::where('UserID', $user->UserID)->max('created_at');

            $lastActivity = collect([$lastPost, $lastReply, $user->created_at])
                ->filter()
                ->map(fn ($d) => Carbon::parse($d))
                ->max();

            // Warnings relevant to the CURRENT inactivity streak only
            // (ignore old warnings from before their last activity)
            $relevantWarnings = Warning::where('UserID', $user->UserID)
                ->where('WarningDate', '>=', $lastActivity)
                ->orderBy('WarningNumber')
                ->get();

            if ($relevantWarnings->isEmpty()) {
                // First warning: 2 weeks of no posts/replies
                if ($now->gte($lastActivity->copy()->addWeeks(2))) {
                    Warning::create([
                        'UserID' => $user->UserID,
                        'WarningNumber' => 1,
                        'WarningDate' => $now,
                    ]);

                    $this->notify($user->UserID, 'You have received a warning for inactivity. Please post or reply soon to remain active.', 'warning');
                    $this->info("Issued Warning #1 to {$user->FullName}");
                }
            } elseif ($relevantWarnings->count() === 1) {
                $warning1 = $relevantWarnings->first();

                // Second warning: 1 week after the first
                if ($now->gte(Carbon::parse($warning1->WarningDate)->addWeek())) {
                    Warning::create([
                        'UserID' => $user->UserID,
                        'WarningNumber' => 2,
                        'WarningDate' => $now,
                    ]);

                    $this->notify($user->UserID, 'This is your second inactivity warning. Continued inactivity will result in a temporary block.', 'warning');
                    $this->info("Issued Warning #2 to {$user->FullName}");
                }
            } elseif ($relevantWarnings->count() >= 2) {
                $warning2 = $relevantWarnings->last();

                // Avoid creating a duplicate blacklist for the same warning cycle
                $alreadyBlacklistedForThisCycle = Blacklist::where('UserID', $user->UserID)
                    ->where('StartDate', '>=', Carbon::parse($warning2->WarningDate)->toDateString())
                    ->exists();

                // Blocking: 3 days after the second warning, for a month
                if (!$alreadyBlacklistedForThisCycle && $now->gte(Carbon::parse($warning2->WarningDate)->addDays(3))) {
                    Blacklist::create([
                        'BlacklistID' => uniqid(),
                        'UserID' => $user->UserID,
                        'Reason' => 'Inactivity - no posts or replies despite two warnings',
                        'StartDate' => $now->toDateString(),
                        'EndDate' => $now->copy()->addMonth()->toDateString(),
                    ]);

                    $this->notify($user->UserID, 'You have been temporarily blocked for one month due to continued inactivity.', 'blacklist');
                    $this->info("Blacklisted {$user->FullName}");
                }
            }
        }
    }

    private function notify($userId, $message, $type)
    {
        Notification::create([
            'NotificationID' => uniqid(),
            'UserID' => $userId,
            'Message' => $message,
            'Type' => $type,
            'Status' => 'Unread',
        ]);
    }
}