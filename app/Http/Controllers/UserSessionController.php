<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserSessionController extends Controller
{
    /**
     * Return active sessions for a specific user as JSON.
     */
    public function index(User $user)
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) {
                $ua = $session->user_agent ?? '';

                return [
                    'id'            => $session->id,
                    'ip_address'    => $session->ip_address ?? 'Unknown',
                    'last_activity' => $session->last_activity
                        ? \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans()
                        : 'Unknown',
                    'last_activity_raw' => $session->last_activity,
                    'device'        => $this->parseDevice($ua),
                    'browser'       => $this->parseBrowser($ua),
                    'os'            => $this->parseOS($ua),
                    'is_current'    => $session->id === session()->getId(),
                ];
            });

        return response()->json([
            'user'     => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar_url,
            ],
            'sessions' => $sessions,
        ]);
    }

    /**
     * Force-logout a specific session for a user.
     */
    public function destroy(User $user, string $sessionId)
    {
        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Session terminated successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Session not found.'], 404);
    }

    /**
     * Force-logout ALL sessions for a user.
     */
    public function destroyAll(User $user)
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return response()->json(['success' => true, 'message' => "All sessions for {$user->name} have been terminated."]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function parseDevice(string $ua): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $ua)) {
            if (preg_match('/iPad/i', $ua)) return 'Tablet';
            return 'Mobile';
        }
        return 'Desktop';
    }

    private function parseBrowser(string $ua): string
    {
        if (str_contains($ua, 'Edg/') || str_contains($ua, 'Edge/'))  return 'Edge';
        if (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera/')) return 'Opera';
        if (str_contains($ua, 'Chrome/'))                              return 'Chrome';
        if (str_contains($ua, 'Firefox/'))                             return 'Firefox';
        if (str_contains($ua, 'Safari/'))                              return 'Safari';
        return 'Unknown';
    }

    private function parseOS(string $ua): string
    {
        if (preg_match('/Windows NT (\d+\.\d+)/i', $ua, $m)) {
            $version = match($m[1]) {
                '10.0' => '10/11', '6.3' => '8.1', '6.2' => '8',
                '6.1' => '7', default => $m[1],
            };
            return "Windows {$version}";
        }
        if (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m)) return 'macOS ' . str_replace('_', '.', $m[1]);
        if (preg_match('/Android ([\d.]+)/i', $ua, $m))  return 'Android ' . $m[1];
        if (preg_match('/iPhone OS ([\d_]+)/i', $ua, $m)) return 'iOS ' . str_replace('_', '.', $m[1]);
        if (str_contains($ua, 'Linux'))                   return 'Linux';
        return 'Unknown';
    }
}
