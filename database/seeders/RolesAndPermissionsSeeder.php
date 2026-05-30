<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'view questions',
            'create questions',
            'edit questions',
            'delete questions',
            'publish questions',
            'review scientific questions',
            'review regulation questions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign created permissions
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super Admin gets all permissions via Gate (usually defined in AuthServiceProvider), but let's give them everything just in case
        $roleSuperAdmin->givePermissionTo(Permission::all());

        $roleExamManager = Role::firstOrCreate(['name' => 'Exam Manager']);
        $roleExamManager->givePermissionTo(Permission::all());

        $roleFieldSecretary = Role::firstOrCreate(['name' => 'Field Secretary']); // دبیر رشته
        $roleFieldSecretary->givePermissionTo(['view questions', 'edit questions', 'publish questions']);

        $roleQuestionDesigner = Role::firstOrCreate(['name' => 'Question Designer']); // طراح سوال
        $roleQuestionDesigner->givePermissionTo(['view questions', 'create questions', 'edit questions']);

        $roleScientificReviewer = Role::firstOrCreate(['name' => 'Scientific Reviewer']); // داور علمی
        $roleScientificReviewer->givePermissionTo(['view questions', 'review scientific questions']);

        $roleRegulationsReviewer = Role::firstOrCreate(['name' => 'Regulations Reviewer']); // داور مقررات
        $roleRegulationsReviewer->givePermissionTo(['view questions', 'review regulation questions']);

        $roleOperator = Role::firstOrCreate(['name' => 'Operator']); // اپراتور
        $roleOperator->givePermissionTo(['view questions']);
    }
}
