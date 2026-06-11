<?php

use App\Models\User;
use App\Models\Question;
use App\Models\Category;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Ensure permissions exist
    $permissions = ['view questions', 'create questions', 'edit questions', 'delete questions'];
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    // Ensure roles exist for testing
    $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $designerRole = Role::firstOrCreate(['name' => 'Question Designer', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Scientific Reviewer', 'guard_name' => 'web']);

    $designerRole->givePermissionTo(['view questions', 'create questions', 'edit questions']);
    $superAdmin->givePermissionTo(['view questions', 'create questions', 'edit questions']);
});

it('restricts designer from accessing admin panel', function () {
    $designer = User::factory()->create();
    $designer->assignRole('Question Designer');

    // Designer panel should be accessible
    $designerUrl = \App\Filament\Resources\Questions\QuestionResource::getUrl('index', panel: 'designer');
    actingAs($designer)
        ->get($designerUrl)
        ->assertSuccessful();

    // Admin panel should be forbidden
    actingAs($designer)
        ->get('/admin')
        ->assertForbidden();
});

it('allows designer to only view and edit their own questions', function () {
    $designer1 = User::factory()->create();
    $designer1->assignRole('Question Designer');

    $designer2 = User::factory()->create();
    $designer2->assignRole('Question Designer');

    $question1 = Question::factory()->create(['user_id' => $designer1->id]);
    $question2 = Question::factory()->create(['user_id' => $designer2->id]);

    // Designer 1 can edit their own question
    $url1 = \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $question1], panel: 'designer');
    actingAs($designer1)
        ->get($url1)
        ->assertSuccessful();

    // Designer 1 cannot edit Designer 2's question
    $url2 = \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $question2], panel: 'designer');
    actingAs($designer1)
        ->get($url2)
        ->assertNotFound(); // Filament returns 404 for Global Scopes / Eloquent Query restrictions
});

it('allows reviewers and admins to see questions in admin panel', function () {
    // Note: User.php canAccessPanel currently only allows Super Admin & Exam Manager into /admin.
    // We will test Super Admin access here based on current code.
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $adminUrl = \App\Filament\Resources\Questions\QuestionResource::getUrl('index', panel: 'admin');
    actingAs($admin)
        ->get($adminUrl)
        ->assertSuccessful();

    $question = Question::factory()->create();

    $url = \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $question], panel: 'admin');
    actingAs($admin)
        ->get($url)
        ->assertSuccessful();
});
