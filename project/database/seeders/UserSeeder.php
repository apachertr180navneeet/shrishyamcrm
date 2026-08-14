<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Agent;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $agentRole = Role::where('name', 'agent')->first();
        $dataEntryRole = Role::where('name', 'data_entry')->first();
        $accountantRole = Role::where('name', 'accountant')->first();

        $agent1 = Agent::where('agent_code', 'AGT-001')->first();

        $users = [
            [
                'email' => 'superadmin@shrishyam.org',
                'first_name' => 'Navneet',
                'last_name' => 'Sharma',
                'full_name' => 'Shri Navneet Sharma',
                'slug' => 'navneet-sharma',
                'phone' => '9876543210',
                'role' => 'super_admin',
                'role_id' => $superAdminRole?->id,
                'agent_id' => null,
            ],
            [
                'email' => 'admin@shrishyam.org',
                'first_name' => 'Mahesh',
                'last_name' => 'Garg',
                'full_name' => 'Mahesh Kumar Garg (Secretary)',
                'slug' => 'mahesh-garg',
                'phone' => '9672056789',
                'role' => 'admin',
                'role_id' => $adminRole?->id,
                'agent_id' => null,
            ],
            [
                'email' => 'agent1@shrishyam.org',
                'first_name' => 'Rameshwar',
                'last_name' => 'Lal Sharma',
                'full_name' => 'Rameshwar Lal Sharma (Agent)',
                'slug' => 'rameshwar-lal-sharma',
                'phone' => '9829012345',
                'role' => 'agent',
                'role_id' => $agentRole?->id,
                'agent_id' => $agent1?->id,
            ],
            [
                'email' => 'dataentry@shrishyam.org',
                'first_name' => 'Sunita',
                'last_name' => 'Devi',
                'full_name' => 'Sunita Devi (Data Entry)',
                'slug' => 'sunita-devi',
                'phone' => '9784045678',
                'role' => 'data_entry',
                'role_id' => $dataEntryRole?->id,
                'agent_id' => null,
            ],
            [
                'email' => 'accountant@shrishyam.org',
                'first_name' => 'Rajendra',
                'last_name' => 'Verma',
                'full_name' => 'Rajendra Verma (Accountant)',
                'slug' => 'rajendra-verma',
                'phone' => '9812034567',
                'role' => 'accountant',
                'role_id' => $accountantRole?->id,
                'agent_id' => null,
            ],
            // Also keep projectadmin@mailinator.com for legacy compatibility
            [
                'email' => 'projectadmin@mailinator.com',
                'first_name' => 'Project',
                'last_name' => 'Admin',
                'full_name' => 'Project Super Admin',
                'slug' => 'project-super-admin',
                'phone' => '8000000000',
                'role' => 'super_admin',
                'role_id' => $superAdminRole?->id,
                'agent_id' => null,
            ],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'first_name' => $u['first_name'],
                    'last_name' => $u['last_name'],
                    'full_name' => $u['full_name'],
                    'slug' => $u['slug'],
                    'password' => Hash::make('123456'),
                    'phone' => $u['phone'],
                    'role' => $u['role'],
                    'role_id' => $u['role_id'],
                    'agent_id' => $u['agent_id'],
                    'address' => 'Lohki, District Mahendragarh, Haryana',
                    'city' => 'Narnaul',
                    'state' => 'Haryana',
                    'country' => 'India',
                    'country_code' => 91,
                    'zipcode' => '123001',
                    'status' => 'active',
                ]
            );

            if ($u['role_id']) {
                $user->roles()->sync([$u['role_id']]);
            }
        }
    }
}
