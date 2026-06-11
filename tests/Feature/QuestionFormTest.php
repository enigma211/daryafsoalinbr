<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Question;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\Questions\Pages\CreateQuestion;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = ['view questions', 'create questions', 'edit questions', 'delete questions'];
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $designerRole = Role::firstOrCreate(['name' => 'Question Designer', 'guard_name' => 'web']);
    $designerRole->givePermissionTo(['view questions', 'create questions', 'edit questions']);

    $this->designer = User::factory()->create();
    $this->designer->assignRole('Question Designer');

    $this->category = Category::factory()->create();

    // Set Filament panel for tests
    filament()->setCurrentPanel(filament()->getPanel('designer'));
});

it('can successfully create a question with valid data', function () {
    actingAs($this->designer);

    $options = [
        ['option_number' => 1, 'text' => 'گزینه اول'],
        ['option_number' => 2, 'text' => 'گزینه دوم'],
        ['option_number' => 3, 'text' => 'گزینه سوم'],
        ['option_number' => 4, 'text' => 'گزینه چهارم'],
    ];

    Livewire::test(CreateQuestion::class)
        ->fillForm([
            'text' => 'متن سوال تستی',
            'category_id' => $this->category->id,
            'edition' => 'اول',
            'discipline' => 'civil',
            'qualification' => 'supervision',
            'exact_source' => 'Page 42',
            'correct_option' => 1,
            'options' => $options,
            'descriptive_answer' => 'توضیحات پاسخ تستی',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('questions', [
        'text' => 'متن سوال تستی',
        'category_id' => $this->category->id,
        'user_id' => $this->designer->id,
    ]);
});

it('validates required fields', function () {
    actingAs($this->designer);

    Livewire::test(CreateQuestion::class)
        ->fillForm([
            'text' => null,
            'category_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'text' => 'required',
            'category_id' => 'required',
        ]);
});

it('validates exactly 4 options are provided', function () {
    actingAs($this->designer);

    $options = [
        ['option_number' => 1, 'text' => 'گزینه اول'],
        ['option_number' => 2, 'text' => 'گزینه دوم'],
        // Missing two options
    ];

    Livewire::test(CreateQuestion::class)
        ->fillForm([
            'text' => 'متن سوال',
            'category_id' => $this->category->id,
            'edition' => 'اول',
            'discipline' => 'civil',
            'qualification' => 'supervision',
            'exact_source' => 'Page 42',
            'options' => $options,
        ])
        ->call('create')
        ->assertHasFormErrors(['options']);
});

it('auto generates a unique code for the question', function () {
    actingAs($this->designer);

    $options = [
        ['option_number' => 1, 'text' => 'گزینه ۱'],
        ['option_number' => 2, 'text' => 'گزینه ۲'],
        ['option_number' => 3, 'text' => 'گزینه ۳'],
        ['option_number' => 4, 'text' => 'گزینه ۴'],
    ];

    $component = Livewire::test(CreateQuestion::class);
    
    // Check that the form state contains a unique_code by default before submission
    $state = $component->get('data');
    expect($state['unique_code'])->not->toBeEmpty();
    expect(strlen($state['unique_code']))->toBe(6);

    $component->fillForm([
            'text' => 'متن سوال یکتا',
            'category_id' => $this->category->id,
            'edition' => 'اول',
            'discipline' => 'civil',
            'qualification' => 'supervision',
            'exact_source' => 'Page 42',
            'correct_option' => 1,
            'options' => $options,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $question = Question::where('text', 'متن سوال یکتا')->first();
    
    expect($question->unique_code)->not->toBeEmpty();
    expect(strlen($question->unique_code))->toBe(6);
});
