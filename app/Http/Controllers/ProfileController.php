<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\SystemAuditLog;

class ProfileController extends Controller
{
    /**
     * Display user profile and login history.
     */
    public function index()
    {
        $currentUser = Auth::user();

        return view('profile.index', compact('currentUser'));
    }

    /**
     * Update permitted personal information (Name, Phone, Designation, Profile Photo).
     * System-controlled fields (Role, College) are protected.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $old = $user->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'designation' => 'nullable|string|max:255',
            'avatar_file' => 'nullable|image|max:2048',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->designation = $validated['designation'];

        if ($request->hasFile('avatar_file')) {
            $uploadDir = public_path('uploads/avatars');
            $storageDir = storage_path('app/public/avatars');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            if (!file_exists($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            $file = $request->file('avatar_file');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            @copy($uploadDir . '/' . $filename, $storageDir . '/' . $filename);
            $user->avatar = 'uploads/avatars/' . $filename;
        }

        $user->save();

        SystemAuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'event_type' => 'profile_update',
            'target_type' => 'User',
            'target_id' => strval($user->id),
            'description' => "User updated personal profile info: {$user->name}",
            'old_values' => $old,
            'new_values' => $user->fresh()->toArray(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('profile')->with('success', 'Your profile information has been updated successfully.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match your existing password.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->force_password_reset = false; // Clear mandatory reset flag once updated
        $user->save();

        SystemAuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'event_type' => 'password_change',
            'target_type' => 'User',
            'target_id' => strval($user->id),
            'description' => "User successfully updated their account password: {$user->name}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('profile')->with('success', 'Your security password has been updated successfully.');
    }
}
