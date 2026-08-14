<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\College;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    private function checkAdminAccess($user, $requiredPermission = null)
    {
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        if ($requiredPermission && $user->hasPermission($requiredPermission)) {
            return true;
        }

        if (!$requiredPermission && (in_array($user->role, ['admin', 'vc', 'pro_vc', 'registrar', 'office_registrar']) || $user->hasPermission('colleges.view') || $user->hasPermission('users.view'))) {
            return true;
        }

        abort(403, "Permission Denied: Your assigned role does not have permission ('{$requiredPermission}') to access this administration module.");
    }

    public function colleges()
    {
        return redirect()->route('superadmin.colleges');
    }

    public function users()
    {
        return redirect()->route('superadmin.users');
    }

    public function settings()
    {
        return redirect()->route('superadmin.settings');
    }
}
