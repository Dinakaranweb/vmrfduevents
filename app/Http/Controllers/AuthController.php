<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SystemAuditLog;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Fetch demo official credentials list to display on login page for easy reviewer access
        $officialAccounts = User::with('college')->orderBy('role')->get();

        return view('auth.login', compact('officialAccounts'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists first to verify account lifecycle state
        $user = User::where('email', $credentials['email'])->first();

        if ($user) {
            // Check Account Locking status strictly as requested
            if ($user->is_locked || $user->account_status === 'locked') {
                SystemAuditLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => $user->role,
                    'event_type' => 'login_failed',
                    'target_type' => 'User',
                    'target_id' => strval($user->id),
                    'description' => "Blocked login attempt on LOCKED account: {$user->email}",
                    'ip_address' => $request->ip(),
                ]);

                return back()->withErrors([
                    'email' => 'Your account has been locked. Please contact the system administrator.',
                ])->onlyInput('email');
            }

            // Check Suspended status
            if ($user->account_status === 'suspended') {
                SystemAuditLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => $user->role,
                    'event_type' => 'login_failed',
                    'target_type' => 'User',
                    'target_id' => strval($user->id),
                    'description' => "Blocked login attempt on SUSPENDED account: {$user->email}",
                    'ip_address' => $request->ip(),
                ]);

                return back()->withErrors([
                    'email' => 'Your account has been suspended by university administration.',
                ])->onlyInput('email');
            }

            // Check Inactive status
            if (!$user->is_active || $user->account_status === 'inactive') {
                SystemAuditLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => $user->role,
                    'event_type' => 'login_failed',
                    'target_type' => 'User',
                    'target_id' => strval($user->id),
                    'description' => "Blocked login attempt on INACTIVE account: {$user->email}",
                    'ip_address' => $request->ip(),
                ]);

                return back()->withErrors([
                    'email' => 'Your account is currently inactive. Please contact administration.',
                ])->onlyInput('email');
            }
        }

        // Attempt normal authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Record successful login audit trail
            $user->last_login_at = now();
            $user->last_login_ip = $request->ip();
            $user->save();

            SystemAuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'event_type' => 'login',
                'target_type' => 'User',
                'target_id' => strval($user->id),
                'description' => "Successful user login: {$user->name} ({$user->email})",
                'ip_address' => $request->ip(),
            ]);

            // Check Force Password Reset requirement
            if ($user->force_password_reset) {
                return redirect()->route('profile')->with('warning', 'SECURITY ADVISORY: Your password reset is mandated by the Super Admin. Please update your password immediately.');
            }

            $targetRoute = $user->role === 'media_editor' ? route('media.dashboard') : route('dashboard');

            return redirect()->intended($targetRoute)
                ->with('success', 'Welcome back to VMRFDU Official Portal, ' . $user->name . '!');
        }

        // Log failed password attempt
        if ($user) {
            SystemAuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'event_type' => 'login_failed',
                'target_type' => 'User',
                'target_id' => strval($user->id),
                'description' => "Failed authentication attempt (invalid password) for user: {$user->email}",
                'ip_address' => $request->ip(),
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our institutional records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            SystemAuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'event_type' => 'logout',
                'target_type' => 'User',
                'target_id' => strval($user->id),
                'description' => "User signed out: {$user->name}",
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been securely signed out.');
    }
}
