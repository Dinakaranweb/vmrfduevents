<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => 'super_admin', 'name' => 'Super Admin', 'description' => 'Full unrestricted system access.', 'is_system' => true],
            ['slug' => 'registrar', 'name' => 'Registrar', 'description' => 'Initial verifier of institutional activity submissions.', 'is_system' => true],
            ['slug' => 'office_registrar', 'name' => 'Office of the Registrar', 'description' => 'Final approval authority for activities.', 'is_system' => true],
            ['slug' => 'vc', 'name' => 'Vice Chancellor', 'description' => 'University-level approval oversight.', 'is_system' => true],
            ['slug' => 'pro_vc', 'name' => 'Pro Vice Chancellor', 'description' => 'Academic approval oversight.', 'is_system' => true],
            ['slug' => 'media_editor', 'name' => 'Media Editor', 'description' => 'Media studio and social broadcasting access.', 'is_system' => true],
            ['slug' => 'college_admin', 'name' => 'College Admin (DEO)', 'description' => 'Constituent college activity submission access.', 'is_system' => true],
            ['slug' => 'viewer', 'name' => 'Viewer', 'description' => 'Read-only reporting and analytics access.', 'is_system' => true],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_system' => $roleData['is_system'],
                    'permissions' => Role::getDefaultPermissions($roleData['slug']),
                    'data_scope' => $roleData['slug'] === 'college_admin' ? 'own_college' : 'all',
                    'is_active' => true,
                ]
            );
        }
    }
}
