<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\College;
use App\Models\ActivitySubmission;
use App\Models\ActivityMedia;
use App\Models\ApprovalLog;
use App\Models\PublishedPost;
use App\Models\Role;
use App\Models\Workflow;
use App\Models\WorkflowRule;
use App\Models\WorkflowStage;
use App\Models\ActivityStatus;
use App\Models\NotificationTemplate;
use App\Models\SystemAuditLog;
use App\Models\SystemSetting;
use App\Models\EventCategory;

class SuperAdminController extends Controller
{
    /**
     * Check if current user has Super Admin authority.
     */
    private function checkSuperAdminAccess($user = null, $requiredPermission = null)
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        if ($requiredPermission && $user->hasPermission($requiredPermission)) {
            return true;
        }

        abort(403, "Permission Denied: Your assigned role ('" . ($user->role_title ?? $user->role) . "') does not have the required explicit privilege ('{$requiredPermission}') to perform this action.");
    }

    /**
     * Helper to log immutable audit trail events.
     */
    private function logAudit($eventType, $targetType, $targetId, $description, $oldValues = null, $newValues = null)
    {
        $user = Auth::user();
        SystemAuditLog::create([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : 'System',
            'user_role' => $user ? $user->role : 'system',
            'event_type' => $eventType,
            'target_type' => $targetType,
            'target_id' => strval($targetId),
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }

    // ==========================================
    // 1. DASHBOARD MODULE
    // ==========================================
    public function dashboard()
    {
        $this->checkSuperAdminAccess(null, 'dashboard.view');

        $totalColleges = College::count();
        $totalUsers = User::count();
        $totalActivities = ActivitySubmission::withTrashed()->count();

        // Status Counts
        $pendingApprovals = ActivitySubmission::whereIn('status', ['verification_pending', 'approval_pending', 'registrar_approval'])->count();
        $approvedActivities = ActivitySubmission::whereIn('status', ['ready_to_publish', 'scheduled'])->count();
        $discussActivities = ActivitySubmission::where('status', 'editor_discuss')->count();
        $rejectedActivities = ActivitySubmission::where('status', 'rejected')->count();
        $publishedCount = ActivitySubmission::where('status', 'published')->count();

        // Social Media Posting Status
        $socialMediaPosts = PublishedPost::with('submission')->latest()->take(10)->get();

        // Recently Submitted Activities
        $recentActivities = ActivitySubmission::with(['college', 'user'])->latest()->take(8)->get();

        // College-wise Activity Report
        $collegeReports = College::withCount([
            'submissions as total_submissions',
            'submissions as published_submissions' => function ($query) {
                $query->where('status', 'published');
            },
            'submissions as pending_submissions' => function ($query) {
                $query->whereIn('status', ['verification_pending', 'approval_pending', 'registrar_approval']);
            }
        ])->get();

        // Monthly & Yearly Analytics (Last 6 Months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('M Y');
            $monthlyData[$monthKey] = ActivitySubmission::withTrashed()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Prepare arrays for Chart.js
        $collegeNamesArray = $collegeReports->pluck('code')->toArray();
        $collegeTotalsArray = $collegeReports->pluck('total_submissions')->toArray();
        $collegePublishedArray = $collegeReports->pluck('published_submissions')->toArray();
        $collegePendingArray = $collegeReports->pluck('pending_submissions')->toArray();

        $monthlyLabelsArray = array_keys($monthlyData);
        $monthlyCountsArray = array_values($monthlyData);

        // Role Breakdown
        $roleCounts = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')->toArray();
        $roleLabelsArray = array_map(function($r) {
            return ucwords(str_replace('_', ' ', $r));
        }, array_keys($roleCounts));
        $roleCountsArray = array_values($roleCounts);
        if (empty($roleLabelsArray)) {
            $roleLabelsArray = ['College Admin', 'Media Editor', 'Registrar', 'Staff'];
            $roleCountsArray = [1, 1, 1, 1];
        }

        // Category Breakdown
        $categoryCounts = ActivitySubmission::withTrashed()
            ->selectRaw('COALESCE(event_category, "General") as cat, count(*) as count')
            ->groupBy('cat')
            ->pluck('count', 'cat')->toArray();
        $categoryLabelsArray = array_keys($categoryCounts);
        $categoryCountsArray = array_values($categoryCounts);
        if (empty($categoryLabelsArray)) {
            $categoryLabelsArray = ['Academic Event', 'Symposium', 'Workshop', 'Outreach'];
            $categoryCountsArray = [0, 0, 0, 0];
        }

        return view('super_admin.dashboard', compact(
            'totalColleges', 'totalUsers', 'totalActivities',
            'pendingApprovals', 'approvedActivities', 'discussActivities', 'rejectedActivities',
            'publishedCount', 'socialMediaPosts', 'recentActivities', 'collegeReports', 'monthlyData',
            'collegeNamesArray', 'collegeTotalsArray', 'collegePublishedArray', 'collegePendingArray',
            'monthlyLabelsArray', 'monthlyCountsArray',
            'roleLabelsArray', 'roleCountsArray',
            'categoryLabelsArray', 'categoryCountsArray'
        ));
    }

    // ==========================================
    // 2. COLLEGE MANAGEMENT MODULE
    // ==========================================
    public function colleges()
    {
        $this->checkSuperAdminAccess(null, 'colleges.view');
        $colleges = College::withCount(['users', 'submissions'])->orderBy('code')->get();
        return view('super_admin.colleges', compact('colleges'));
    }

    public function storeCollege(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'colleges.create');
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:colleges',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'dean_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $college = College::create($validated);
        $this->logAudit('college_management', 'College', $college->id, "Created college: {$college->name} ({$college->code})", null, $college->toArray());

        return redirect()->route('superadmin.colleges')->with('success', 'College created successfully.');
    }

    public function updateCollege(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'colleges.edit');
        $college = College::findOrFail($id);
        $old = $college->toArray();

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:colleges,code,' . $college->id,
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'dean_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
        ]);

        $college->update($validated);
        $this->logAudit('college_management', 'College', $college->id, "Updated college: {$college->name}", $old, $college->fresh()->toArray());

        return redirect()->route('superadmin.colleges')->with('success', 'College updated successfully.');
    }

    public function deleteCollege($id)
    {
        $this->checkSuperAdminAccess(null, 'colleges.delete');
        $college = College::findOrFail($id);
        $name = $college->name;
        $college->delete();

        $this->logAudit('college_management', 'College', $id, "Deleted college: {$name}");
        return redirect()->route('superadmin.colleges')->with('success', 'College deleted successfully.');
    }

    public function toggleCollege($id)
    {
        $this->checkSuperAdminAccess(null, 'colleges.edit');
        $college = College::findOrFail($id);
        $college->is_active = !$college->is_active;
        $college->save();

        $statusText = $college->is_active ? 'Activated' : 'Deactivated';
        $this->logAudit('college_management', 'College', $id, "{$statusText} college: {$college->name}");

        return redirect()->route('superadmin.colleges')->with('success', "College {$statusText} successfully.");
    }

    // ==========================================
    // 2.5. EVENT CATEGORIES MASTER CRUD
    // ==========================================
    public function categories()
    {
        $this->checkSuperAdminAccess(null, 'settings.view');
        $categories = EventCategory::orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        return view('super_admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:event_categories,name',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $category = EventCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
            'sort_order' => $validated['sort_order'] ?? (EventCategory::count() + 1),
        ]);

        $this->logAudit('category_management', 'EventCategory', $category->id, "Created event category: {$category->name}");
        return redirect()->route('superadmin.categories')->with('success', 'New Event Category created successfully.');
    }

    public function updateCategory(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $category = EventCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:event_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
        ]);

        $this->logAudit('category_management', 'EventCategory', $category->id, "Updated event category: {$category->name}");
        return redirect()->route('superadmin.categories')->with('success', 'Event Category updated successfully.');
    }

    public function deleteCategory($id)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $category = EventCategory::findOrFail($id);
        $name = $category->name;
        $category->delete();

        $this->logAudit('category_management', 'EventCategory', $id, "Deleted event category: {$name}");
        return redirect()->route('superadmin.categories')->with('success', "Event Category '{$name}' deleted successfully.");
    }

    public function toggleCategory($id)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $category = EventCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        $statusText = $category->is_active ? 'Activated' : 'Deactivated';
        $this->logAudit('category_management', 'EventCategory', $id, "{$statusText} category: {$category->name}");
        return redirect()->route('superadmin.categories')->with('success', "Category {$statusText} successfully.");
    }

    public function reorderCategories(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        if ($request->filled('items') && is_array($request->items)) {
            foreach ($request->items as $idx => $id) {
                EventCategory::where('id', $id)->update(['sort_order' => $idx + 1]);
            }
        }
        return response()->json(['success' => true]);
    }

    // ==========================================
    // 3. USER MANAGEMENT MODULE
    // ==========================================
    public function users()
    {
        $this->checkSuperAdminAccess(null, 'users.view');
        $users = User::with(['college'])->orderBy('name')->get();
        $colleges = College::orderBy('code')->get();
        $roles = Role::where('is_active', true)->get();
        return view('super_admin.users', compact('users', 'colleges', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'users.create');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'college_id' => 'nullable|exists:colleges,id',
            'designation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'account_status' => 'nullable|in:active,inactive,locked,suspended',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['account_status'] = $validated['account_status'] ?? 'active';
        $validated['is_locked'] = ($validated['account_status'] === 'locked');
        $validated['is_active'] = ($validated['account_status'] === 'active');
        $validated['force_password_reset'] = $request->boolean('force_password_reset');

        $user = User::create($validated);

        $this->logAudit('user_management', 'User', $user->id, "Created user: {$user->name} ({$user->email}) with status {$user->account_status}");
        return redirect()->route('superadmin.users')->with('success', 'User account created successfully.');
    }

    public function updateUser(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'users.edit');
        $user = User::findOrFail($id);
        $old = $user->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'college_id' => 'nullable|exists:colleges,id',
            'designation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'account_status' => 'nullable|in:active,inactive,locked,suspended',
        ]);

        if (isset($validated['account_status'])) {
            $validated['is_locked'] = ($validated['account_status'] === 'locked');
            $validated['is_active'] = ($validated['account_status'] === 'active');
        }
        $validated['force_password_reset'] = $request->boolean('force_password_reset');

        $user->update($validated);
        $this->logAudit('user_management', 'User', $user->id, "Updated profile/role/status for user: {$user->name}", $old, $user->fresh()->toArray());

        return redirect()->route('superadmin.users')->with('success', 'User updated successfully.');
    }

    public function resetPassword(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'users.reset_password');
        $user = User::findOrFail($id);
        $request->validate(['new_password' => 'required|string|min:6']);

        $user->password = Hash::make($request->new_password);
        $user->force_password_reset = $request->boolean('force_password_reset', true); // Mandate reset on next login by default when admin resets password
        $user->save();

        $this->logAudit('user_management', 'User', $user->id, "Reset password for user: {$user->name} (Force Reset: " . ($user->force_password_reset ? 'Yes' : 'No') . ")");
        return redirect()->route('superadmin.users')->with('success', "Password reset successfully for {$user->name}.");
    }

    public function toggleLock($id)
    {
        $this->checkSuperAdminAccess(null, 'users.edit');
        $user = User::findOrFail($id);
        $user->is_locked = !$user->is_locked;
        $user->account_status = $user->is_locked ? 'locked' : 'active';
        $user->is_active = !$user->is_locked;
        $user->save();

        $action = $user->is_locked ? 'Locked' : 'Unlocked';
        $this->logAudit('user_management', 'User', $user->id, "{$action} account for user: {$user->name}");

        return redirect()->route('superadmin.users')->with('success', "User account {$action} successfully.");
    }

    public function changeAccountStatus(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'users.edit');
        $user = User::findOrFail($id);
        $oldStatus = $user->account_status;

        $request->validate([
            'account_status' => 'required|in:active,inactive,locked,suspended',
        ]);

        $user->account_status = $request->account_status;
        $user->is_locked = ($user->account_status === 'locked');
        $user->is_active = ($user->account_status === 'active');
        $user->save();

        $this->logAudit('user_management', 'User', $user->id, "Changed lifecycle state of user {$user->name} from {$oldStatus} to {$user->account_status}");
        return redirect()->route('superadmin.users')->with('success', "Account status updated to " . strtoupper($user->account_status) . ".");
    }

    public function transferUser(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'users.transfer');
        $user = User::findOrFail($id);
        $oldCollege = $user->college_id;

        $request->validate(['new_college_id' => 'required|exists:colleges,id']);
        $user->college_id = $request->new_college_id;
        $user->save();

        $this->logAudit('user_management', 'User', $user->id, "Transferred user {$user->name} from college ID {$oldCollege} to {$user->college_id}");
        return redirect()->route('superadmin.users')->with('success', 'User transferred successfully.');
    }

    // ==========================================
    // 4. ENTERPRISE ACCESS CONTROL & PERMISSION SUITE
    // ==========================================
    public function accessControl(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'roles.view');
        $roles = Role::orderBy('id')->get();
        $availablePermissions = Role::getAvailablePermissions();
        
        $selectedUserId = $request->input('user_id');
        $selectedUser = $selectedUserId ? User::find($selectedUserId) : null;
        $users = User::with('college')->orderBy('role')->orderBy('name')->get();
        $colleges = College::where('is_active', true)->get();

        return view('super_admin.access_control', compact(
            'roles', 'availablePermissions', 'users', 'selectedUser', 'colleges'
        ));
    }

    public function updateUserPermissions(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'roles.manage');
        $user = User::findOrFail($id);
        if ($user->role === 'super_admin') {
            return redirect()->route('superadmin.accessControl', ['tab' => 'users'])->with('error', 'Super Admin permissions are global and cannot be modified.');
        }

        if ($request->has('reset_overrides') || $request->input('reset_overrides') == '1') {
            $user->custom_permissions = null;
            $user->save();
            $this->logAudit('permission_management', 'User', $user->id, "Reset custom permission overrides for user: {$user->name}");
            return redirect()->route('superadmin.accessControl', ['tab' => 'users', 'user_id' => $user->id])->with('success', "Custom permission overrides cleared. {$user->name} now inherits 100% dynamically from role ({$user->role}).");
        }

        $customPerms = $request->input('custom_permissions', []);
        $user->custom_permissions = !empty($customPerms) ? $customPerms : null;
        $user->save();

        $this->logAudit('permission_management', 'User', $user->id, "Updated individual custom permission overrides for user: {$user->name} ({$user->email})");

        return redirect()->route('superadmin.accessControl', ['tab' => 'users', 'user_id' => $user->id])->with('success', "Custom permission overrides updated for {$user->name}.");
    }

    public function copyRolePermissions(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'roles.manage');
        $request->validate([
            'source_role_id' => 'required|exists:roles,id',
            'target_role_id' => 'required|exists:roles,id|different:source_role_id',
        ]);

        $sourceRole = Role::findOrFail($request->source_role_id);
        $targetRole = Role::findOrFail($request->target_role_id);

        if ($targetRole->slug === 'super_admin') {
            return redirect()->route('superadmin.accessControl')->with('error', 'Cannot alter core Super Admin permissions.');
        }

        $perms = is_array($sourceRole->permissions) && !empty($sourceRole->permissions)
            ? $sourceRole->permissions
            : Role::getDefaultPermissions($sourceRole->slug);

        $targetRole->update(['permissions' => $perms]);
        \Illuminate\Support\Facades\Cache::forget('role_perms_' . $targetRole->slug);

        $this->logAudit('role_management', 'Role', $targetRole->id, "Copied permissions from role {$sourceRole->name} to {$targetRole->name}");

        return redirect()->route('superadmin.accessControl', ['tab' => 'roles'])->with('success', "Successfully copied all permissions from {$sourceRole->name} to {$targetRole->name}.");
    }

    // ==========================================
    // ROLE MANAGEMENT MODULE (LEGACY & API)
    // ==========================================
    public function roles()
    {
        $this->checkSuperAdminAccess(null, 'roles.view');
        $roles = Role::orderBy('id')->get();
        $availablePermissions = Role::getAvailablePermissions();
        return view('super_admin.roles', compact('roles', 'availablePermissions'));
    }

    public function storeRole(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'roles.manage');
        $validated = $request->validate([
            'slug' => 'required|string|unique:roles',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'data_scope' => 'required|in:all,college,self',
            'permissions' => 'nullable|array',
        ]);

        $validated['permissions'] = $request->input('permissions', []);
        $role = Role::create($validated);
        \Illuminate\Support\Facades\Cache::forget('role_perms_' . $role->slug);
        $this->logAudit('role_management', 'Role', $role->id, "Created dynamic role: {$role->name} ({$role->slug})");

        return redirect()->route('superadmin.roles')->with('success', 'Role created successfully.');
    }

    public function updateRole(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'roles.manage');
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'data_scope' => 'required|in:all,college,self',
            'permissions' => 'nullable|array',
        ]);

        $validated['permissions'] = $request->input('permissions', []);
        $role->update($validated);
        \Illuminate\Support\Facades\Cache::forget('role_perms_' . $role->slug);
        $this->logAudit('role_management', 'Role', $role->id, "Updated role permissions: {$role->name}");

        if (str_contains(url()->previous(), 'access-control')) {
            return redirect()->route('superadmin.accessControl', ['tab' => 'roles'])->with('success', "Role permissions for '{$role->name}' updated dynamically.");
        }

        return redirect()->route('superadmin.roles')->with('success', 'Role permissions updated successfully.');
    }

    public function deleteRole($id)
    {
        $this->checkSuperAdminAccess(null, 'roles.manage');
        $role = Role::findOrFail($id);
        if ($role->is_system) {
            return redirect()->route('superadmin.roles')->with('error', 'Cannot delete system core roles.');
        }
        \Illuminate\Support\Facades\Cache::forget('role_perms_' . $role->slug);
        $role->delete();
        $this->logAudit('role_management', 'Role', $id, "Deleted role: {$role->name}");

        return redirect()->route('superadmin.roles')->with('success', 'Role deleted successfully.');
    }

    // ==========================================
    // 5. UNRESTRICTED ACTIVITY MANAGEMENT MODULE
    // ==========================================
    public function activities(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'activities.view');
        $query = ActivitySubmission::withTrashed()->with(['college', 'user', 'media']);

        if ($request->college_id) {
            $query->where('college_id', $request->college_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $activities = $query->latest()->paginate(20);
        $colleges = College::orderBy('code')->get();
        $users = User::orderBy('name')->get();
        $statuses = ActivityStatus::where('is_active', true)->get();

        return view('super_admin.activities', compact('activities', 'colleges', 'users', 'statuses'));
    }

    public function overrideStatus(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'activities.edit');
        $submission = ActivitySubmission::withTrashed()->findOrFail($id);
        $oldStatus = $submission->status;

        $request->validate([
            'status' => 'required|string',
            'stage' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $submission->status = $request->status;
        $submission->current_stage = $request->stage;
        if ($request->remarks) {
            $submission->admin_remarks = $request->remarks;
        }
        $submission->save();

        ApprovalLog::create([
            'activity_submission_id' => $submission->id,
            'user_id' => Auth::id(),
            'action' => 'super_admin_override',
            'from_status' => $oldStatus,
            'to_status' => $submission->status,
            'remarks' => $request->remarks ?? 'Super Admin Forced Status Override',
        ]);

        $this->logAudit('status_change', 'ActivitySubmission', $submission->id, "Overrode status of activity {$submission->reference_id} from {$oldStatus} to {$submission->status}");
        return redirect()->back()->with('success', 'Activity status and stage overridden successfully.');
    }

    public function reassignOwner(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'activities.edit');
        $submission = ActivitySubmission::withTrashed()->findOrFail($id);
        $request->validate(['new_user_id' => 'required|exists:users,id']);

        $newUser = User::find($request->new_user_id);
        $oldUserId = $submission->user_id;
        $submission->user_id = $newUser->id;
        if ($newUser->college_id) {
            $submission->college_id = $newUser->college_id;
        }
        $submission->save();

        $this->logAudit('activity_update', 'ActivitySubmission', $submission->id, "Reassigned activity {$submission->reference_id} from User ID {$oldUserId} to {$newUser->name}");
        return redirect()->back()->with('success', 'Activity reassigned successfully.');
    }

    public function forceResubmit($id)
    {
        $this->checkSuperAdminAccess(null, 'activities.edit');
        $submission = ActivitySubmission::withTrashed()->findOrFail($id);
        $submission->status = 'verification_pending';
        $submission->current_stage = 'College Verifier (Force Resubmitted)';
        $submission->save();

        ApprovalLog::create([
            'activity_submission_id' => $submission->id,
            'user_id' => Auth::id(),
            'action' => 'force_resubmit',
            'remarks' => 'Super Admin Forced Resubmission into Workflow',
        ]);

        $this->logAudit('workflow_change', 'ActivitySubmission', $submission->id, "Force resubmitted activity {$submission->reference_id}");
        return redirect()->back()->with('success', 'Activity reopened and force resubmitted into workflow.');
    }

    public function restoreActivity($id)
    {
        $this->checkSuperAdminAccess(null, 'activities.edit');
        $submission = ActivitySubmission::withTrashed()->findOrFail($id);
        $submission->restore();

        $this->logAudit('activity_update', 'ActivitySubmission', $id, "Restored soft-deleted activity {$submission->reference_id}");
        return redirect()->back()->with('success', 'Activity restored successfully.');
    }

    public function forceDeleteActivity($id)
    {
        $this->checkSuperAdminAccess(null, 'activities.delete');
        $submission = ActivitySubmission::withTrashed()->findOrFail($id);
        $ref = $submission->reference_id;
        $submission->delete(); // Soft delete

        $this->logAudit('activity_delete', 'ActivitySubmission', $id, "Soft deleted activity {$ref}");
        return redirect()->back()->with('success', 'Activity deleted successfully.');
    }

    // ==========================================
    // 6. WORKFLOW & STATUS MANAGEMENT MODULE
    // ==========================================
    public function workflows(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'workflows.view');

        $workflows = Workflow::withCount('stages')->orderBy('is_default', 'desc')->orderBy('name')->get();
        $selectedWorkflowId = $request->input('workflow_id') ?: ($workflows->firstWhere('is_default', true)?->id ?? $workflows->first()?->id);
        $activeWorkflow = Workflow::find($selectedWorkflowId);

        $stages = $activeWorkflow ? WorkflowStage::where('workflow_id', $activeWorkflow->id)->orderBy('stage_order')->get() : collect();
        $roles = Role::where('is_active', true)->get();
        $users = User::orderBy('name')->get();
        $colleges = College::orderBy('code')->get();
        $statuses = ActivityStatus::orderBy('id')->get();
        $rules = WorkflowRule::with(['workflow', 'college'])->orderBy('priority', 'desc')->get();

        // Analytics & Bottleneck Metrics
        $dashboardMetrics = [
            'total_pending' => ActivitySubmission::where('status', '!=', 'published')->where('status', '!=', 'rejected')->count(),
            'stage_counts' => [],
            'bottlenecks' => []
        ];

        if ($activeWorkflow) {
            foreach ($stages as $s) {
                $count = ActivitySubmission::where('current_stage', $s->stage_order)->count();
                $dashboardMetrics['stage_counts'][] = [
                    'stage_name' => $s->name,
                    'count' => $count,
                    'role' => $s->role_slug
                ];
                if ($count >= 5) {
                    $dashboardMetrics['bottlenecks'][] = $s;
                }
            }
        }

        return view('super_admin.workflows', compact('workflows', 'activeWorkflow', 'stages', 'roles', 'users', 'colleges', 'statuses', 'rules', 'dashboardMetrics'));
    }

    // Workflows Master CRUD
    public function storeWorkflowMaster(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workflow = Workflow::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
            'is_default' => Workflow::count() === 0,
        ]);

        $this->logAudit('workflow_change', 'Workflow', $workflow->id, "Created workflow master: {$workflow->name}");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $workflow->id])->with('success', 'New approval workflow created.');
    }

    public function updateWorkflowMaster(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $workflow = Workflow::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workflow->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        $this->logAudit('workflow_change', 'Workflow', $workflow->id, "Updated workflow master: {$workflow->name}");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $workflow->id])->with('success', 'Workflow configuration updated.');
    }

    public function deleteWorkflowMaster($id)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $workflow = Workflow::findOrFail($id);
        if ($workflow->is_default) {
            return redirect()->back()->with('error', 'Cannot delete the default active workflow. Please set another workflow as default first.');
        }

        $name = $workflow->name;
        WorkflowStage::where('workflow_id', $id)->delete();
        WorkflowRule::where('workflow_id', $id)->delete();
        $workflow->delete();

        $this->logAudit('workflow_change', 'Workflow', $id, "Deleted workflow master: {$name}");
        return redirect()->route('superadmin.workflows')->with('success', "Workflow '{$name}' deleted.");
    }

    public function duplicateWorkflowMaster(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $workflow = Workflow::findOrFail($id);
        $newWorkflow = $workflow->duplicate($request->input('new_name'));

        $this->logAudit('workflow_change', 'Workflow', $newWorkflow->id, "Duplicated workflow from {$workflow->name}");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $newWorkflow->id])->with('success', "Duplicated workflow successfully created.");
    }

    public function setDefaultWorkflowMaster($id)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        Workflow::query()->update(['is_default' => false]);
        $workflow = Workflow::findOrFail($id);
        $workflow->update(['is_default' => true, 'is_active' => true]);

        $this->logAudit('workflow_change', 'Workflow', $workflow->id, "Set default workflow to {$workflow->name}");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $workflow->id])->with('success', "Default approval workflow updated to '{$workflow->name}'.");
    }

    // Stage Management
    public function storeWorkflowStage(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'stage_order' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'roles' => 'nullable|array',
            'role_slug' => 'nullable|string',
            'users' => 'nullable|array',
            'college_id' => 'nullable|exists:colleges,id',
            'designation' => 'nullable|string',
            'allowed_actions' => 'nullable|array',
            'approval_condition' => 'nullable|string',
            'status_on_approve' => 'nullable|string',
            'status_on_reject' => 'nullable|string',
            'status_on_discuss' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $rolesInput = $request->input('roles') ?: $request->input('role_slug');
        $roleSlugValue = is_array($rolesInput) ? json_encode(array_values($rolesInput)) : $rolesInput;
        $usersValue = $request->filled('users') ? json_encode(array_values($request->input('users'))) : null;
        $actionsValue = $request->filled('allowed_actions') ? json_encode(array_values($request->input('allowed_actions'))) : json_encode(['approve', 'discuss', 'reject', 'return', 'forward', 'hold', 'reassign']);

        $stage = WorkflowStage::create([
            'workflow_id' => $validated['workflow_id'],
            'stage_order' => $validated['stage_order'],
            'name' => $validated['name'],
            'role_slug' => $roleSlugValue ?: 'editor',
            'user_ids' => $usersValue,
            'college_id' => $validated['college_id'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'is_mandatory' => $request->has('is_mandatory'),
            'allow_parallel' => $request->has('allow_parallel'),
            'allowed_actions' => $actionsValue,
            'approval_condition' => $validated['approval_condition'] ?? 'any_one',
            'status_on_approve' => $validated['status_on_approve'] ?? null,
            'status_on_reject' => $validated['status_on_reject'] ?? null,
            'status_on_discuss' => $validated['status_on_discuss'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        $this->logAudit('workflow_change', 'WorkflowStage', $stage->id, "Added stage {$stage->name} to workflow ID {$validated['workflow_id']}");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $validated['workflow_id']])->with('success', 'Workflow stage created.');
    }

    public function updateWorkflowStage(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $stage = WorkflowStage::findOrFail($id);

        $validated = $request->validate([
            'stage_order' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'roles' => 'nullable|array',
            'role_slug' => 'nullable|string',
            'users' => 'nullable|array',
            'college_id' => 'nullable|exists:colleges,id',
            'designation' => 'nullable|string',
            'allowed_actions' => 'nullable|array',
            'approval_condition' => 'nullable|string',
            'status_on_approve' => 'nullable|string',
            'status_on_reject' => 'nullable|string',
            'status_on_discuss' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $rolesInput = $request->input('roles') ?: $request->input('role_slug');
        $roleSlugValue = is_array($rolesInput) ? json_encode(array_values($rolesInput)) : ($rolesInput ?: $stage->role_slug);
        $usersValue = $request->filled('users') ? json_encode(array_values($request->input('users'))) : null;
        $actionsValue = $request->filled('allowed_actions') ? json_encode(array_values($request->input('allowed_actions'))) : json_encode(['approve', 'discuss', 'reject', 'return', 'forward', 'hold', 'reassign']);

        $stage->update([
            'stage_order' => $validated['stage_order'],
            'name' => $validated['name'],
            'role_slug' => $roleSlugValue,
            'user_ids' => $usersValue,
            'college_id' => $validated['college_id'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'is_mandatory' => $request->has('is_mandatory'),
            'allow_parallel' => $request->has('allow_parallel'),
            'allowed_actions' => $actionsValue,
            'approval_condition' => $validated['approval_condition'] ?? 'any_one',
            'status_on_approve' => $validated['status_on_approve'] ?? null,
            'status_on_reject' => $validated['status_on_reject'] ?? null,
            'status_on_discuss' => $validated['status_on_discuss'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $this->logAudit('workflow_change', 'WorkflowStage', $stage->id, "Updated stage {$stage->name}");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $stage->workflow_id])->with('success', 'Workflow stage updated.');
    }

    public function deleteWorkflowStage($id)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $stage = WorkflowStage::findOrFail($id);
        $workflowId = $stage->workflow_id;
        $name = $stage->name;
        $stage->delete();

        // Re-index remaining stages for this workflow
        $remaining = WorkflowStage::where('workflow_id', $workflowId)->orderBy('stage_order')->get();
        foreach ($remaining as $idx => $s) {
            $s->update(['stage_order' => $idx + 1]);
        }

        $this->logAudit('workflow_change', 'WorkflowStage', $id, "Deleted stage {$name}");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $workflowId])->with('success', 'Workflow stage deleted and orders re-indexed.');
    }

    public function reorderWorkflowStages(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');

        if ($request->filled('stage_id') && $request->filled('direction')) {
            $current = WorkflowStage::findOrFail($request->stage_id);
            if ($request->direction === 'up') {
                $swap = WorkflowStage::where('workflow_id', $current->workflow_id)->where('stage_order', '<', $current->stage_order)->orderBy('stage_order', 'desc')->first();
            } else {
                $swap = WorkflowStage::where('workflow_id', $current->workflow_id)->where('stage_order', '>', $current->stage_order)->orderBy('stage_order', 'asc')->first();
            }

            if ($swap) {
                $oldOrder = $current->stage_order;
                $current->update(['stage_order' => $swap->stage_order]);
                $swap->update(['stage_order' => $oldOrder]);
            }
            return redirect()->route('superadmin.workflows', ['workflow_id' => $current->workflow_id])->with('success', 'Stage sequence reordered.');
        }

        return redirect()->back();
    }

    public function syncVisualWorkflow(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'stages' => 'required|array',
        ]);

        $workflowId = $validated['workflow_id'];
        $stages = $validated['stages'];

        DB::transaction(function() use ($workflowId, $stages, $request) {
            foreach ($stages as $index => $sData) {
                $order = $index + 1;
                if (!empty($sData['id']) && is_numeric($sData['id'])) {
                    $stage = WorkflowStage::where('workflow_id', $workflowId)->where('id', $sData['id'])->first();
                    if ($stage) {
                        $stage->update([
                            'stage_order' => $order,
                            'name' => $sData['name'] ?? $stage->name,
                            'role_slug' => isset($sData['roles']) ? (is_array($sData['roles']) ? json_encode(array_values($sData['roles'])) : $sData['roles']) : $stage->role_slug,
                            'is_mandatory' => isset($sData['is_mandatory']) ? filter_var($sData['is_mandatory'], FILTER_VALIDATE_BOOLEAN) : $stage->is_mandatory,
                            'is_active' => isset($sData['is_active']) ? filter_var($sData['is_active'], FILTER_VALIDATE_BOOLEAN) : $stage->is_active,
                            'allow_parallel' => isset($sData['allow_parallel']) ? filter_var($sData['allow_parallel'], FILTER_VALIDATE_BOOLEAN) : $stage->allow_parallel,
                            'approval_condition' => $sData['approval_condition'] ?? $stage->approval_condition,
                        ]);
                    }
                } else {
                    // Create new stage added dynamically from the visual builder palette
                    $rolesInput = $sData['roles'] ?? 'editor';
                    $roleSlugValue = is_array($rolesInput) ? json_encode(array_values($rolesInput)) : $rolesInput;
                    WorkflowStage::create([
                        'workflow_id' => $workflowId,
                        'stage_order' => $order,
                        'name' => $sData['name'] ?? ('Stage ' . $order),
                        'role_slug' => $roleSlugValue ?: 'editor',
                        'is_mandatory' => isset($sData['is_mandatory']) ? filter_var($sData['is_mandatory'], FILTER_VALIDATE_BOOLEAN) : true,
                        'is_active' => isset($sData['is_active']) ? filter_var($sData['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                        'allow_parallel' => isset($sData['allow_parallel']) ? filter_var($sData['allow_parallel'], FILTER_VALIDATE_BOOLEAN) : false,
                        'allowed_actions' => json_encode(['approve', 'discuss', 'reject', 'return', 'forward', 'hold']),
                        'approval_condition' => $sData['approval_condition'] ?? 'any_one',
                        'description' => $sData['description'] ?? 'Dynamically added workflow stage',
                    ]);
                }
            }

            // Remove any stages from this workflow that were deleted on the visual canvas if explicit delete requested
            if ($request->has('delete_missing') && filter_var($request->input('delete_missing'), FILTER_VALIDATE_BOOLEAN)) {
                $keptIds = collect($stages)->pluck('id')->filter(fn($id) => is_numeric($id))->toArray();
                if (!empty($keptIds)) {
                    WorkflowStage::where('workflow_id', $workflowId)->whereNotIn('id', $keptIds)->delete();
                } else {
                    WorkflowStage::where('workflow_id', $workflowId)->delete();
                }
            }
        });

        $this->logAudit('workflow_change', 'Workflow', $workflowId, "Synced visual drag-and-drop workflow flowchart sequence");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Visual workflow flowchart sequence and stage conditions published successfully.'
            ]);
        }

        return redirect()->route('superadmin.workflows', ['workflow_id' => $workflowId])->with('success', 'Visual workflow flowchart sequence published successfully.');
    }

    public function seedExampleWorkflow(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $defaultWorkflow = Workflow::firstOrCreate(
            ['is_default' => true],
            ['name' => 'Standard Institutional Workflow', 'description' => '5-stage institutional approval & publication chain', 'is_active' => true]
        );

        WorkflowStage::where('workflow_id', $defaultWorkflow->id)->delete();

        $exampleStages = [
            [
                'workflow_id' => $defaultWorkflow->id,
                'stage_order' => 1,
                'name' => 'Principal of College',
                'role_slug' => 'principal',
                'is_mandatory' => true,
                'allow_parallel' => false,
                'allowed_actions' => json_encode(['approve', 'discuss', 'reject', 'forward']),
                'approval_condition' => 'any_one',
                'description' => 'Initial verification & endorsement by College Principal'
            ],
            [
                'workflow_id' => $defaultWorkflow->id,
                'stage_order' => 2,
                'name' => 'Approver (VC / Pro-VC / Registrar)',
                'role_slug' => json_encode(['vc', 'provc', 'registrar']),
                'is_mandatory' => true,
                'allow_parallel' => true,
                'allowed_actions' => json_encode(['approve', 'discuss', 'reject']),
                'approval_condition' => 'any_one',
                'description' => 'Central leadership oversight & executive sign-off'
            ],
            [
                'workflow_id' => $defaultWorkflow->id,
                'stage_order' => 3,
                'name' => 'Media Desk Only For Edit',
                'role_slug' => 'editor',
                'is_mandatory' => true,
                'allow_parallel' => false,
                'allowed_actions' => json_encode(['approve', 'discuss']),
                'approval_condition' => 'any_one',
                'description' => 'Media studio inspection, curation, and attachment editing'
            ],
            [
                'workflow_id' => $defaultWorkflow->id,
                'stage_order' => 4,
                'name' => 'Approval to PA to Registrar',
                'role_slug' => 'pa_registrar',
                'is_mandatory' => true,
                'allow_parallel' => false,
                'allowed_actions' => json_encode(['approve', 'discuss', 'reject', 'forward']),
                'approval_condition' => 'any_one',
                'description' => 'Specialized administrative clearance by PA to Registrar'
            ],
            [
                'workflow_id' => $defaultWorkflow->id,
                'stage_order' => 5,
                'name' => 'Then Media Can Publish',
                'role_slug' => 'editor',
                'is_mandatory' => true,
                'allow_parallel' => false,
                'allowed_actions' => json_encode(['approve', 'discuss']),
                'approval_condition' => 'any_one',
                'description' => 'Final media sign-off and public repository broadcast'
            ],
        ];

        foreach ($exampleStages as $s) {
            WorkflowStage::create($s);
        }

        $this->logAudit('workflow_change', 'Workflow', $defaultWorkflow->id, "Seeded 5-stage standard institutional workflow");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $defaultWorkflow->id])->with('success', 'Standard 5-stage institutional workflow seeded.');
    }

    // Assignment Rules CRUD
    public function storeWorkflowRule(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'college_id' => 'nullable|exists:colleges,id',
            'category' => 'nullable|string',
            'activity_type' => 'nullable|string',
            'priority' => 'required|integer',
        ]);

        WorkflowRule::create($validated);
        $this->logAudit('workflow_change', 'WorkflowRule', null, "Added workflow assignment rule");
        return redirect()->route('superadmin.workflows', ['workflow_id' => $validated['workflow_id']])->with('success', 'Workflow assignment rule created.');
    }

    public function deleteWorkflowRule($id)
    {
        $this->checkSuperAdminAccess(null, 'workflows.manage');
        $rule = WorkflowRule::findOrFail($id);
        $rule->delete();

        $this->logAudit('workflow_change', 'WorkflowRule', $id, "Deleted workflow assignment rule");
        return redirect()->back()->with('success', 'Workflow assignment rule deleted.');
    }

    public function storeStatus(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $validated = $request->validate([
            'slug' => 'required|string|unique:activity_statuses',
            'name' => 'required|string|max:255',
            'badge_color' => 'required|string',
            'description' => 'nullable|string',
        ]);

        ActivityStatus::create($validated);
        $this->logAudit('status_change', 'ActivityStatus', null, "Created status master: {$validated['name']}");
        return redirect()->route('superadmin.workflows')->with('success', 'Activity status created.');
    }

    // ==========================================
    // 7. DATA MAPPING MATRIX MODULE
    // ==========================================
    public function mappings()
    {
        $this->checkSuperAdminAccess(null, 'roles.manage');
        $users = User::with(['college'])->orderBy('name')->get();
        $colleges = College::orderBy('code')->get();
        $roles = Role::where('is_active', true)->get();
        return view('super_admin.mappings', compact('users', 'colleges', 'roles'));
    }

    public function updateMapping(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'roles.manage');
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'college_id' => 'nullable|exists:colleges,id',
            'role' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update([
            'college_id' => $request->college_id,
            'role' => $request->role,
        ]);

        $this->logAudit('user_management', 'User', $user->id, "Updated mapping matrix for user {$user->name}");
        return redirect()->route('superadmin.mappings')->with('success', 'User mapping updated successfully.');
    }

    // ==========================================
    // 8. NOTIFICATION MANAGER MODULE
    // ==========================================
    public function notifications()
    {
        $this->checkSuperAdminAccess(null, 'settings.view');
        $templates = NotificationTemplate::orderBy('event_key')->get();
        return view('super_admin.notifications', compact('templates'));
    }

    public function updateTemplate(Request $request, $id)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $template = NotificationTemplate::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email_subject' => 'nullable|string',
            'email_body' => 'nullable|string',
            'system_title' => 'nullable|string',
            'system_message' => 'nullable|string',
        ]);

        $template->update($validated);
        $this->logAudit('general', 'NotificationTemplate', $template->id, "Updated notification template: {$template->name}");
        return redirect()->route('superadmin.notifications')->with('success', 'Notification template saved.');
    }

    // ==========================================
    // 9. AUDIT TRAIL MODULE
    // ==========================================
    public function auditLogs(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'audits.view');
        $query = SystemAuditLog::latest();

        if ($request->event_type) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->paginate(25);
        $users = User::orderBy('name')->get();
        return view('super_admin.audit_logs', compact('logs', 'users'));
    }

    // ==========================================
    // 10. REPORTS & EXPORTER MODULE
    // ==========================================
    public function reports(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'analytics.view');
        $query = ActivitySubmission::withTrashed()->with(['college', 'user']);

        if ($request->college_id) {
            $query->where('college_id', $request->college_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('activity_date', [$request->start_date, $request->end_date]);
        }

        $activities = $query->latest()->paginate(30);
        $colleges = College::orderBy('code')->get();
        $statuses = ActivityStatus::where('is_active', true)->get();

        return view('super_admin.reports', compact('activities', 'colleges', 'statuses'));
    }

    public function exportReports(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'analytics.export');
        $query = ActivitySubmission::withTrashed()->with(['college', 'user']);

        if ($request->college_id) {
            $query->where('college_id', $request->college_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $records = $query->get();
        $csvFileName = 'Super_Admin_Activity_Report_' . date('Y_m_d_H_i_s') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ref ID', 'Title', 'College Code', 'Submitted By', 'Activity Date', 'Category', 'Status', 'Current Stage', 'Created At']);
            foreach ($records as $row) {
                fputcsv($file, [
                    $row->reference_id,
                    $row->title,
                    $row->college ? $row->college->code : 'N/A',
                    $row->user ? $row->user->name : 'N/A',
                    $row->activity_date,
                    $row->event_category,
                    $row->status,
                    $row->current_stage,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // 11. DYNAMIC MASTER SETTINGS MODULE
    // ==========================================
    public function masterSettings()
    {
        $this->checkSuperAdminAccess(null, 'settings.view');
        $settings = SystemSetting::orderBy('group')->get();
        $portalLogo = SystemSetting::getValue('portal_logo');
        $defaultAvatar = SystemSetting::getValue('default_avatar');
        $portalName = SystemSetting::getValue('portal_name', 'VMRFDU Activity Portal');
        $portalTagline = SystemSetting::getValue('portal_tagline', 'Activity Verification & Tracking');
        $portalHeaderTitle = SystemSetting::getValue('portal_header_title', 'Institutional Activity Submissions & Verification Desk');
        $portalFooterText = SystemSetting::getValue('portal_footer_text', '© ' . date('Y') . ' Vinayaka Mission\'s Research Foundation (DU). All rights reserved.');
        $portalFooterSystemText = SystemSetting::getValue('portal_footer_system_text', 'Institutional Activity Verification Engine');
        $portalPrimaryColor = SystemSetting::getValue('portal_primary_color', '#1e293b');
        $portalAccentColor = SystemSetting::getValue('portal_accent_color', '#2563eb');
        $portalCardRadius = SystemSetting::getValue('portal_card_radius', '16px');
        $portalCardBorder = SystemSetting::getValue('portal_card_border', '#e2e8f0');
        $helplineNumber = SystemSetting::getValue('helpline_number', '1800-VMRF-HELP');
        $sidebarIcons = json_decode(SystemSetting::getValue('sidebar_icons', '{}'), true);
        
        // Login Page Settings are now handled in loginSettings()

        $ntpStatus = \App\Services\NtpTimeService::getStatus();

        return view('super_admin.settings', compact(
            'settings', 'portalLogo', 'defaultAvatar', 'portalName', 'portalTagline',
            'portalHeaderTitle', 'portalFooterText', 'portalFooterSystemText',
            'portalPrimaryColor', 'portalAccentColor', 'portalCardRadius', 'portalCardBorder',
            'sidebarIcons', 'ntpStatus', 'helplineNumber'
        ));
    }

    public function loginSettings()
    {
        $this->checkSuperAdminAccess(null, 'settings.view');
        
        $loginTitle = SystemSetting::getValue('login_title', 'Institutional Login - VMRFDU Centralized Portal');
        $loginBannerHeader = SystemSetting::getValue('login_banner_header', 'VMRFDU Official Portal');
        $loginBannerSubheader = SystemSetting::getValue('login_banner_subheader', 'Central Administration');
        $loginBannerMainText = SystemSetting::getValue('login_banner_main_text', 'Centralized Activity & Media Publication');
        $loginBannerParagraph = SystemSetting::getValue('login_banner_paragraph', 'A unified institutional platform designed for constituent colleges across Salem, Chennai, and Puducherry to submit, verify, approve, and broadcast university events and research milestones.');
        $loginFormTitle = SystemSetting::getValue('login_form_title', 'Sign In');
        $loginFormSubtitle = SystemSetting::getValue('login_form_subtitle', 'Enter your institutional credentials to access the activity portal.');
        
        $loginShowDirectory = filter_var(SystemSetting::getValue('login_show_directory', 'true'), FILTER_VALIDATE_BOOLEAN);
        $loginAnnouncement = SystemSetting::getValue('login_announcement', '');
        $loginAnnouncementType = SystemSetting::getValue('login_announcement_type', 'info');
        $loginSupportEmail = SystemSetting::getValue('login_support_email', '');
        $loginHelpdeskUrl = SystemSetting::getValue('login_helpdesk_url', '');
        $loginFooterText = SystemSetting::getValue('login_footer_text', SystemSetting::getValue('portal_footer_text', '© ' . date('Y') . ' Vinayaka Mission\'s Research Foundation (DU). All rights reserved.'));
        $loginFooterSystemText = SystemSetting::getValue('login_footer_system_text', SystemSetting::getValue('portal_footer_system_text', 'Institutional Activity Verification Engine'));

        return view('super_admin.login_settings', compact(
            'loginTitle', 'loginBannerHeader', 'loginBannerSubheader', 'loginBannerMainText',
            'loginBannerParagraph', 'loginFormTitle', 'loginFormSubtitle',
            'loginShowDirectory', 'loginAnnouncement', 'loginAnnouncementType', 'loginSupportEmail', 'loginHelpdeskUrl',
            'loginFooterText', 'loginFooterSystemText'
        ));
    }

    public function updateLoginSettings(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');

        if ($request->has('login_title')) {
            SystemSetting::setValue('login_title', trim($request->login_title), 'branding', 'Login Page Browser Title');
        }
        if ($request->has('login_banner_header')) {
            SystemSetting::setValue('login_banner_header', trim($request->login_banner_header), 'branding', 'Login Banner Top Header');
        }
        if ($request->has('login_banner_subheader')) {
            SystemSetting::setValue('login_banner_subheader', trim($request->login_banner_subheader), 'branding', 'Login Banner Subheader');
        }
        if ($request->has('login_banner_main_text')) {
            SystemSetting::setValue('login_banner_main_text', trim($request->login_banner_main_text), 'branding', 'Login Banner Main Heading Text');
        }
        if ($request->has('login_banner_paragraph')) {
            SystemSetting::setValue('login_banner_paragraph', trim($request->login_banner_paragraph), 'branding', 'Login Banner Description Paragraph');
        }
        if ($request->has('login_form_title')) {
            SystemSetting::setValue('login_form_title', trim($request->login_form_title), 'branding', 'Login Form Title');
        }
        if ($request->has('login_form_subtitle')) {
            SystemSetting::setValue('login_form_subtitle', trim($request->login_form_subtitle), 'branding', 'Login Form Subtitle');
        }

        // Handle Checkbox (If absent, it's false)
        SystemSetting::setValue('login_show_directory', $request->has('login_show_directory') ? 'true' : 'false', 'branding', 'Show Official Access Directory');

        if ($request->has('login_announcement')) {
            SystemSetting::setValue('login_announcement', trim($request->login_announcement), 'branding', 'Login Announcement Text');
        }
        if ($request->has('login_announcement_type')) {
            SystemSetting::setValue('login_announcement_type', trim($request->login_announcement_type), 'branding', 'Login Announcement Color Type');
        }
        if ($request->has('login_support_email')) {
            SystemSetting::setValue('login_support_email', trim($request->login_support_email), 'branding', 'Login Support Email');
        }
        if ($request->has('login_helpdesk_url')) {
            SystemSetting::setValue('login_helpdesk_url', trim($request->login_helpdesk_url), 'branding', 'Login Helpdesk URL');
        }
        if ($request->has('login_footer_text')) {
            SystemSetting::setValue('login_footer_text', trim($request->login_footer_text), 'branding', 'Login Left Banner Footer Text');
        }
        if ($request->has('login_footer_system_text')) {
            SystemSetting::setValue('login_footer_system_text', trim($request->login_footer_system_text), 'branding', 'Login Left Banner System Text');
        }

        $this->logAudit('general', 'SystemSetting', null, "Updated login page customization configurations");
        return redirect()->route('superadmin.settings.login')->with('success', 'Login page settings updated successfully.');
    }

    public function updateSetting(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        SystemSetting::setValue($request->key, $request->value, $request->group ?? 'general', $request->description);

        $this->logAudit('general', 'SystemSetting', null, "Updated setting key: {$request->key}");
        return redirect()->route('superadmin.settings')->with('success', 'Master setting updated successfully.');
    }

    public function updateSidebarIcons(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        
        // $request->icons will be an array of menu_key => ['icon' => 'bi-...', 'color' => '#...']
        $iconsData = $request->input('icons', []);
        
        SystemSetting::setValue('sidebar_icons', json_encode($iconsData), 'branding', 'JSON configuration for sidebar navigation icons and colors.');
        
        $this->logAudit('general', 'SystemSetting', null, "Updated sidebar icons configuration");
        return redirect()->route('superadmin.settings')->with('success', 'Sidebar icons updated successfully.');
    }

    public function updateBrandingSettings(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $request->validate([
            'portal_name' => 'nullable|string|max:100',
            'portal_tagline' => 'nullable|string|max:150',
            'portal_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'default_avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        // Create upload directory if it does not exist
        $uploadDir = public_path('uploads/settings');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if ($request->hasFile('portal_logo_file')) {
            $file = $request->file('portal_logo_file');
            $filename = 'portal_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            SystemSetting::setValue('portal_logo', 'uploads/settings/' . $filename, 'branding', 'Main institutional portal logo');
        } elseif ($request->has('remove_portal_logo')) {
            SystemSetting::setValue('portal_logo', null, 'branding', 'Main institutional portal logo');
        }

        if ($request->hasFile('default_avatar_file')) {
            $file = $request->file('default_avatar_file');
            $filename = 'default_avatar_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            SystemSetting::setValue('default_avatar', 'uploads/settings/' . $filename, 'branding', 'Default user profile avatar');
        } elseif ($request->has('remove_default_avatar')) {
            SystemSetting::setValue('default_avatar', null, 'branding', 'Default user profile avatar');
        }

        if ($request->has('portal_name')) {
            SystemSetting::setValue('portal_name', trim($request->portal_name), 'branding', 'Main portal title');
        }

        if ($request->has('portal_tagline')) {
            SystemSetting::setValue('portal_tagline', trim($request->portal_tagline), 'branding', 'Portal subtitle tagline');
        }

        if ($request->has('portal_header_title')) {
            SystemSetting::setValue('portal_header_title', trim($request->portal_header_title), 'branding', 'Top navbar main title');
        }

        if ($request->has('portal_footer_text')) {
            SystemSetting::setValue('portal_footer_text', trim($request->portal_footer_text), 'branding', 'Portal footer copyright text');
        }

        if ($request->has('portal_footer_system_text')) {
            SystemSetting::setValue('portal_footer_system_text', trim($request->portal_footer_system_text), 'branding', 'Portal footer system engine tagline');
        }

        if ($request->has('helpline_number')) {
            SystemSetting::setValue('helpline_number', trim($request->helpline_number), 'general', 'Portal Helpline Number');
        }

        if ($request->has('portal_primary_color')) {
            SystemSetting::setValue('portal_primary_color', trim($request->portal_primary_color), 'branding', 'Theme primary color (Sidebar/Headers)');
        }

        if ($request->has('portal_accent_color')) {
            SystemSetting::setValue('portal_accent_color', trim($request->portal_accent_color), 'branding', 'Theme accent brand color (Buttons/Links/Card accents)');
        }

        if ($request->has('portal_card_radius')) {
            SystemSetting::setValue('portal_card_radius', trim($request->portal_card_radius), 'branding', 'Card border radius (Corner curvature)');
        }
        if ($request->has('portal_card_border')) {
            SystemSetting::setValue('portal_card_border', trim($request->portal_card_border), 'branding', 'Card border color');
        }

        $this->logAudit('general', 'SystemSetting', null, "Updated enterprise branding configurations");
        return redirect()->route('superadmin.settings')->with('success', 'Portal branding, header, footer, colors and card design settings updated successfully.');
    }

    public function updateNtpSettings(Request $request)
    {
        $this->checkSuperAdminAccess(null, 'settings.edit');
        $request->validate([
            'ntp_server' => 'required|string|max:150',
            'ntp_port' => 'required|integer|min:1|max:65535',
            'timezone' => 'required|string|max:100',
        ]);

        SystemSetting::setValue('ntp_server', trim($request->ntp_server), 'ntp', 'NTP Protocol Server');
        SystemSetting::setValue('ntp_port', trim($request->ntp_port), 'ntp', 'NTP Protocol UDP Port');
        SystemSetting::setValue('timezone', trim($request->timezone), 'ntp', 'System Default Timezone');

        \App\Services\NtpTimeService::refreshOffset();

        $this->logAudit('general', 'SystemSetting', null, "Updated NTP Synchronization protocol server ({$request->ntp_server}:{$request->ntp_port}) and timezone ({$request->timezone})");
        return redirect()->route('superadmin.settings')->with('success', "NTP Protocol settings updated and resynchronized with {$request->ntp_server}:{$request->ntp_port} (+05:30 IST).");
    }
}
