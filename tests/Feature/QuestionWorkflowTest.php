<?php

use App\Models\User;
use App\Models\Question;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\Questions\Pages\EditQuestion;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $permissions = ['view questions', 'create questions', 'edit questions', 'delete questions'];
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $designerRole = Role::firstOrCreate(['name' => 'Question Designer', 'guard_name' => 'web']);
    $reviewerRole = Role::firstOrCreate(['name' => 'Scientific Reviewer', 'guard_name' => 'web']);
    $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

    $designerRole->givePermissionTo(['view questions', 'create questions', 'edit questions']);
    $reviewerRole->givePermissionTo(['view questions', 'edit questions']);
    $superAdminRole->givePermissionTo(['view questions', 'create questions', 'edit questions', 'delete questions']);

    $this->designer = User::factory()->create();
    $this->designer->assignRole('Question Designer');

    $this->reviewer = User::factory()->create();
    $this->reviewer->assignRole('Scientific Reviewer');
});

it('sets the default status of a newly created question to draft', function () {
    /** @var \Tests\TestCase $this */
    $question = Question::factory()->create(['user_id' => $this->designer->id]);

    expect($question->current_status)->toBe('draft');
});

it('allows reviewer to save reviewer notes', function () {
    /** @var \Tests\TestCase $this */
    filament()->setCurrentPanel(filament()->getPanel('admin'));
    actingAs($this->reviewer);

    $category = \App\Models\Category::factory()->create();
    $this->reviewer->categories()->attach($category);

    $question = Question::factory()->create([
        'user_id' => $this->designer->id,
        'category_id' => $category->id,
        'current_status' => 'awaiting_review',
        'reviewer_notes' => null,
        'edition' => 'اول',
        'discipline' => 'civil',
        'qualification' => 'supervision',
        'exact_source' => 'Page 42',
    ]);

    for ($i = 1; $i <= 4; $i++) {
        $question->options()->create([
            'option_number' => $i,
            'text' => "Option $i",
        ]);
    }

    Livewire::test(EditQuestion::class, ['record' => $question->getRouteKey()])
        ->fillForm([
            'reviewer_notes' => 'این سوال نیاز به اصلاح در بخش گزینه‌ها دارد.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'reviewer_notes' => 'این سوال نیاز به اصلاح در بخش گزینه‌ها دارد.',
    ]);
});

it('updates status to needs_revision when reject action is called', function () {
    /** @var \Tests\TestCase $this */
    filament()->setCurrentPanel(filament()->getPanel('admin'));
    actingAs($this->reviewer);

    $category = \App\Models\Category::factory()->create();
    $this->reviewer->categories()->attach($category);

    $question = Question::factory()->create([
        'user_id' => $this->designer->id,
        'category_id' => $category->id,
        'current_status' => 'awaiting_review',
    ]);

    Livewire::test(EditQuestion::class, ['record' => $question->getRouteKey()])
        ->callAction('reject_or_revise', data: [
            'comment' => 'سوال گنگ است.',
        ]);

    expect($question->refresh()->current_status)->toBe('needs_revision');
    
    // Check if the comment was saved
    $this->assertDatabaseHas('question_comments', [
        'question_id' => $question->id,
        'comment' => 'سوال گنگ است.',
        'user_id' => $this->reviewer->id,
    ]);
});

it('prevents designer from editing an approved question', function () {
    /** @var \Tests\TestCase $this */
    filament()->setCurrentPanel(filament()->getPanel('designer'));
    actingAs($this->designer);

    $question = Question::factory()->create([
        'user_id' => $this->designer->id,
        'current_status' => 'approved',
    ]);

    $url = \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $question], panel: 'designer');

    actingAs($this->designer)
        ->get($url)
        ->assertForbidden(); // The policy restricts editing approved questions
});
