<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$role = \App\Models\Role::where('slug', 'admin')->first();
$role->permissions = ['workflows.view', 'workflows.manage'];
$role->save();
\Illuminate\Support\Facades\Cache::forget('role_perms_admin');
$user = \App\Models\User::where('role', 'admin')->first();
echo $user->hasPermission('workflows.view') ? 'YES' : 'NO';
