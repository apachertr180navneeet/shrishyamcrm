<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Members
            ['name' => 'member.view', 'display_name' => 'View Members', 'group' => 'members'],
            ['name' => 'member.create', 'display_name' => 'Create Member', 'group' => 'members'],
            ['name' => 'member.edit', 'display_name' => 'Edit Member', 'group' => 'members'],
            ['name' => 'member.delete', 'display_name' => 'Delete Member', 'group' => 'members'],
            // Payments & Collections
            ['name' => 'payment.view', 'display_name' => 'View Payments', 'group' => 'payments'],
            ['name' => 'payment.create', 'display_name' => 'Record Payment', 'group' => 'payments'],
            ['name' => 'receipt.view', 'display_name' => 'View Receipts', 'group' => 'payments'],
            ['name' => 'receipt.generate', 'display_name' => 'Generate Receipts', 'group' => 'payments'],
            ['name' => 'ledger.view', 'display_name' => 'View Financial Ledgers', 'group' => 'payments'],
            // Masters
            ['name' => 'scheme.manage', 'display_name' => 'Manage Schemes', 'group' => 'masters'],
            ['name' => 'age_slab.manage', 'display_name' => 'Manage Age Slabs', 'group' => 'masters'],
            ['name' => 'agent.manage', 'display_name' => 'Manage Agents', 'group' => 'masters'],
            // Events & Payouts
            ['name' => 'event.manage', 'display_name' => 'Manage Society Events', 'group' => 'events'],
            ['name' => 'payout.create', 'display_name' => 'Create Payout', 'group' => 'payouts'],
            ['name' => 'payout.approve', 'display_name' => 'Approve Payout', 'group' => 'payouts'],
            // Reports & Settings
            ['name' => 'report.view', 'display_name' => 'View Reports', 'group' => 'reports'],
            ['name' => 'report.export', 'display_name' => 'Export Reports', 'group' => 'reports'],
            ['name' => 'user.manage', 'display_name' => 'Manage Users & Roles', 'group' => 'system'],
            ['name' => 'setting.manage', 'display_name' => 'Manage Society Settings', 'group' => 'system'],
            ['name' => 'audit.view', 'display_name' => 'View Audit Logs', 'group' => 'system'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $p) {
            $createdPermissions[$p['name']] = Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        $allPermissionNames = array_keys($createdPermissions);

        // Only 2 Roles: Admin & Agent
        $roles = [
            'admin' => [
                'display_name' => 'Admin',
                'display_name_hindi' => 'प्रशासक / एडमिन',
                'description' => 'Full administrative privileges and operational management across all modules.',
                'permissions' => $allPermissionNames,
            ],
            'agent' => [
                'display_name' => 'Agent',
                'display_name_hindi' => 'प्रतिनिधि / एजेंट',
                'description' => 'Field agent access to view and create assigned members, collect payments, and view receipts.',
                'permissions' => [
                    'member.view', 'member.create',
                    'payment.view', 'payment.create', 'receipt.view', 'receipt.generate', 'ledger.view'
                ],
            ],
        ];

        foreach ($roles as $key => $r) {
            $role = Role::updateOrCreate(['name' => $key], [
                'display_name' => $r['display_name'],
                'display_name_hindi' => $r['display_name_hindi'],
                'description' => $r['description'],
            ]);

            $permissionIds = [];
            foreach ($r['permissions'] as $pName) {
                if (isset($createdPermissions[$pName])) {
                    $permissionIds[] = $createdPermissions[$pName]->id;
                }
            }
            $role->permissions()->sync($permissionIds);
        }
    }
}
