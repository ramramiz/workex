<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppAlert;
use App\Models\AppAlertUser;
use App\Models\User;
use Illuminate\Http\Request;

class AppAlertController extends Controller
{
    public function index()
    {
        $alerts = AppAlert::with('creator')->latest()->paginate(10);
        return view('admin.alerts.index', compact('alerts'));
    }

    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get();
        return view('admin.alerts.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'heading' => 'required|string|max:255',
            'title' => 'required|string',
            'target' => 'required|in:all,selected',
            'users' => 'required_if:target,selected|array',
            'users.*' => 'exists:users,id',
        ]);

        $alert = AppAlert::create([
            'heading' => $request->heading,
            'title' => $request->title,
            'created_by' => auth()->id(),
        ]);

        if ($request->target === 'all') {
            $userIds = User::where('id', '!=', auth()->id())->pluck('id')->toArray();
        } else {
            $userIds = $request->users;
        }

        foreach ($userIds as $userId) {
            AppAlertUser::create([
                'app_alert_id' => $alert->id,
                'user_id' => $userId,
                'confirmed_at' => null,
            ]);
        }

        return redirect()->route('admin.alerts.index')->with('success', 'Global alert sent successfully.');
    }

    public function destroy(AppAlert $alert)
    {
        $alert->delete();
        return redirect()->route('admin.alerts.index')->with('success', 'Global alert deleted.');
    }

    public function captchaCode()
    {
        $code = (string) rand(10, 99);
        session(['alert_captcha' => $code]);

        // Generate encrypted token for race-condition-free validation
        $payload = json_encode([
            'code' => $code,
            'expires_at' => time() + 300,
        ]);
        $token = encrypt($payload);

        return response()->json([
            'code' => $code,
            'token' => $token,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'alert_id' => 'required|exists:app_alerts,id',
            'captcha' => 'required|string',
            'token' => 'nullable|string',
        ]);

        $verified = false;

        // Try token verification first
        if ($request->filled('token')) {
            try {
                $payload = json_decode(decrypt($request->token), true);
                if ($payload && isset($payload['code']) && isset($payload['expires_at'])) {
                    if (time() <= $payload['expires_at'] && $request->captcha === $payload['code']) {
                        $verified = true;
                    }
                }
            } catch (\Throwable $e) {
                // skip and check fallback session
            }
        }

        // Fallback to session check
        if (!$verified) {
            $sessionCaptcha = session('alert_captcha');
            if ($sessionCaptcha && $request->captcha === $sessionCaptcha) {
                $verified = true;
            }
        }

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'The entered number is incorrect. Please try again.',
            ], 422);
        }

        $alertUser = AppAlertUser::where('app_alert_id', $request->alert_id)
            ->where('user_id', auth()->id())
            ->whereNull('confirmed_at')
            ->first();

        if ($alertUser) {
            $alertUser->update([
                'confirmed_at' => now(),
            ]);
            session()->forget('alert_captcha');
            
            return response()->json([
                'success' => true,
                'message' => 'Confirmed successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Alert not found or already confirmed.',
        ], 404);
    }

    public function checkActive()
    {
        if (!auth()->check()) {
            return response()->json(['has_alert' => false]);
        }

        $unconfirmedAlert = AppAlert::whereHas('users', function ($q) {
            $q->where('user_id', auth()->id())
              ->whereNull('confirmed_at');
        })->latest()->first();

        if ($unconfirmedAlert) {
            return response()->json([
                'has_alert' => true,
                'alert_id' => $unconfirmedAlert->id,
                'heading' => $unconfirmedAlert->heading,
                'title' => $unconfirmedAlert->title,
            ]);
        }

        return response()->json(['has_alert' => false]);
    }
}
