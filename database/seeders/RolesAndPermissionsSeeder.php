<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // https://spatie.be/docs/laravel-permission/v5/advanced-usage/seeding
        // 1. Reset cached roles and permissions
        // 2. create permissions
        // 3. create roles and assign created permissions

        Log::info('called');
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

//        \DB::update('DELETE FROM permissions');   // Holds the names of the permissions in your app.
//        \DB::update('DELETE FROM roles');   // Holds the names of the roles in your app.
//        \DB::update('TRUNCATE TABLE role_has_permissions'); // Holds data showing the permissions that each role has.
//        \DB::update('TRUNCATE TABLE model_has_roles'); // Holds data showing which roles your specific model instances (e.g., User) have.
//        \DB::update('TRUNCATE TABLE model_has_permissions'); // Holds data showing which permissions your specific model instances (e.g., User) have.

        // Create permissions
        // impersonate permission
        Permission::firstOrCreate(['group' => 'auth', 'name' => 'impersonate']);

        // model permissions
        $collection = collect([
            'user',
            'role', 'permission', 'user_model_has_permission',
            'tenant', 'client', 'branch', 'fleet', 'vehicle',
            'meter_reading', 'preventive_maintenance', 'repair_order', 'fueling', 'lineitem', 'warranty', 'vehicle_meter_reading',
            'vendor',
            'vehicle_type', 'repair_order_type', 'lineitem_type', 'lineitem_category', 'preventive_maintenance_template','engine_manufacturer',
            'fueltype','fuelunit_type','repair_order_status',
            'system_meter_type', 'system_p_m_due_type', 'system_vehicle_type',
            'cost_per_mile_detailed','cost_per_mile_summary','invoice_summary','fuel_tax',
            'file_import', 'integration', 'integration_run', 'integration_log'
            // ... // List all your Models you want to have Permissions for.
        ]);
        $collection->each(function ($item, $key) {
            // create permissions for each collection item CRUDI
            Permission::firstOrCreate(['group' => $item, 'name' => 'view ' . $item]);       // R
            Permission::firstOrCreate(['group' => $item, 'name' => 'view any ' . $item]);   // I
            Permission::firstOrCreate(['group' => $item, 'name' => 'manage ' . $item]);     // C,U,D
            Permission::firstOrCreate(['group' => $item, 'name' => 'manage any ' . $item]); //C,U,D
            Permission::firstOrCreate(['group' => $item, 'name' => 'restore ' . $item]);
            Permission::firstOrCreate(['group' => $item, 'name' => 'forceDelete ' . $item]);
        });

        //Create roles and assign permissions
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());

        $collection = collect(['tenant-admin', 'client-manager', 'branch-manager', 'fleet-manager']);
        $collection->each(function ($item, $key) {
            $level = $item;
            $role = Role::firstOrCreate(['name' => $level]);
            RolesAndPermissionsSeeder::assignPermissions($role, $level, false);
            $role = Role::firstOrCreate(['name' => $level . '-read-only']);
            RolesAndPermissionsSeeder::assignPermissions($role, $level, true);
        });


        // Give Default Users Super-Admin Role
        $user = User::whereEmail('scott@tranzitfleet.com')->first();
        $user->assignRole('super-admin');
        Log::info($user);
        $user = User::whereEmail('developer@tranzitfleet.com')->first();
        $user->assignRole('super-admin');
    }

    private static function assignPermissions($role, $level, $viewOnly) {
        if ($level === 'tenant-admin' || $level === 'client-manager' || $level === 'branch-manager' || $level === 'fleet-manager') {
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'vehicle', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'meter_reading', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'preventive_maintenance', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'repair_order', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'fueling', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'lineitem', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'warranty', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'file_import', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'vehicle_meter_reading', $viewOnly);

            // Now give permissions for read only items so views with dropdowns will work
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'client');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'branch');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'fleet');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'vendor');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'vehicle_type');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'repair_order_type');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'lineitem_type');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'lineitem_category');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'preventive_maintenance_template');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'engine_manufacturer');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'fueltype');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'fuelunit_type');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'repair_order_status');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'system_meter_type');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'system_p_m_due_type');
            RolesAndPermissionsSeeder::giveReadOnlyPermissionsForModel($role, 'system_vehicle_type');
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'cost_per_mile_detailed', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'cost_per_mile_summary', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'invoice_summary', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'fuel_tax', $viewOnly);
        }
        if ($level === 'tenant-admin' || $level === 'client-manager' || $level === 'branch-manager') {
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'fleet', $viewOnly);
        }
        if ($level === 'tenant-admin' || $level === 'client-manager') {
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'branch', $viewOnly);
        }
        if ($level === 'tenant-admin') {
            $role->givePermissionTo(['impersonate']);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'client', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'user', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'vendor', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'vehicle_type', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'repair_order_type', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'lineitem_type', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'lineitem_category', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'preventive_maintenance_template', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'engine_manufacturer', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'fueltype', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'fuelunit_type', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'repair_order_status', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'user_model_has_permission', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'system_meter_type', true);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'system_p_m_due_type', true);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'system_vehicle_type', true);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'role', true);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'permission', true);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'tenant', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'integration', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'integration_log', $viewOnly);
            RolesAndPermissionsSeeder::givePermissionsForModel($role, 'integration_run', $viewOnly);
        }
        //Super Admin Only and are accounted for in $role->givePermissionTo(Permission::all());
    }

    private static function givePermissionsForModel($role, $item, $viewOnly) {
        if ($viewOnly) {
            $role->givePermissionTo([
                'view ' . $item,
                'view any ' . $item,
            ]);
        }
        else {
            $role->givePermissionTo([
                'view ' . $item,
                'view any ' . $item,
                'manage ' . $item,
                'manage any ' . $item,
                'restore ' . $item,
                'forceDelete ' . $item
            ]);
        }
    }

    private static function giveReadOnlyPermissionsForModel($role, $item) {
        $role->givePermissionTo([
            'view ' . $item,
            'view any ' . $item,
        ]);
    }

}
