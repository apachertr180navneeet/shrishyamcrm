<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\SocietySetting;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $agentRole = Role::where('name', 'agent')->first();

        $users = [
            [
                'email' => 'admin@shrishyam.org',
                'first_name' => 'Society',
                'last_name' => 'Admin',
                'full_name' => 'Society Admin',
                'slug' => 'society-admin',
                'phone' => '9876543210',
                'role' => 'admin',
                'role_id' => $adminRole?->id,
            ],
            [
                'email' => 'projectadmin@mailinator.com',
                'first_name' => 'Project',
                'last_name' => 'Admin',
                'full_name' => 'Project Admin',
                'slug' => 'project-admin',
                'phone' => '8000000000',
                'role' => 'admin',
                'role_id' => $adminRole?->id,
            ],
            [
                'email' => 'agent@shrishyam.org',
                'first_name' => 'Field',
                'last_name' => 'Agent',
                'full_name' => 'Field Agent',
                'slug' => 'field-agent',
                'phone' => '9876543211',
                'role' => 'agent',
                'role_id' => $agentRole?->id,
            ],
        ];

        foreach ($users as $u) {
            $user = User::where('email', $u['email'])->orWhere('phone', $u['phone'])->first();
            $data = [
                'email' => $u['email'],
                'first_name' => $u['first_name'],
                'last_name' => $u['last_name'],
                'full_name' => $u['full_name'],
                'slug' => $u['slug'],
                'password' => Hash::make('123456'),
                'phone' => $u['phone'],
                'role' => $u['role'],
                'role_id' => $u['role_id'],
                'agent_id' => null,
                'address' => 'Lohki, District Mahendragarh, Haryana',
                'city' => 'Narnaul',
                'state' => 'Haryana',
                'country' => 'India',
                'country_code' => 91,
                'zipcode' => '123001',
                'status' => 'active',
            ];

            if ($user) {
                $user->update($data);
            } else {
                $user = User::create($data);
            }

            if ($u['role_id']) {
                $user->roles()->sync([$u['role_id']]);
            }
        }

        // Initialize default Society Settings
        $settings = [
            'society_name' => 'Shri Shyam Welfare Society',
            'society_name_hindi' => 'श्री श्याम वेलफेयर सोसायटी लोहीकी',
            'reg_no' => 'HR/019/2021/04582',
            'san_prefix' => 'SAN-LOH',
            'address' => 'Main Bazar, Lohki, District Mahendragarh, Haryana - 123001',
            'phone' => '+91 98290 12345',
            'email' => 'info@shrishyamwelfare.org',
            'president_name' => 'Shri Navneet Sharma',
            'secretary_name' => 'Shri Mahesh Garg',
            'treasurer_name' => 'Shri Rameshwar Lal',
            'default_event_rate' => '200',
            'default_commission' => '5',
        ];

        foreach ($settings as $key => $val) {
            SocietySetting::setVal($key, $val);
        }
    }
}
