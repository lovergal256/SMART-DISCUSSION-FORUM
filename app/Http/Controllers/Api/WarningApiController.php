<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WarningApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $warnings = $user->warnings()->orderByDesc('WarningDate')->get()->map(function ($w) {
            return [
                'WarningID' => $w->WarningID,
                'WarningNumber' => $w->WarningNumber,
                'WarningDate' => \Carbon\Carbon::parse($w->WarningDate)->toIso8601String(),
            ];
        });

        $activeBlacklist = $user->blacklists()
            ->where('EndDate', '>=', now()->toDateString())
            ->first();

        return response()->json([
            'warnings' => $warnings,
            'activeBlacklist' => $activeBlacklist ? [
                'BlacklistID' => $activeBlacklist->BlacklistID,
                'Reason' => $activeBlacklist->Reason,
                'StartDate' => $activeBlacklist->StartDate,
                'EndDate' => $activeBlacklist->EndDate,
            ] : null,
        ]);
    }
}