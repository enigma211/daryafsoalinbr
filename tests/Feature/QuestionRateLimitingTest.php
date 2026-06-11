<?php

use App\Models\User;
use App\Models\Question;
use App\Models\SystemSetting;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\Questions\Pages\CreateQuestion;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Filament\Notifications\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $permissions = ['view questions', 'create questions', 'edit questions', 'delete questions'];
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $designerRole = Role::firstOrCreate(['name' => 'Question Designer', 'guard_name' => 'web']);
    $designerRole->givePermissionTo(['view questions', 'create questions', 'edit questions']);
    
    $this->designer = User::factory()->create();
    $this->designer->assignRole('Question Designer');

    $this->category = \App\Models\Category::factory()->create();

    filament()->setCurrentPanel(filament()->getPanel('designer'));
});

it('prevents submission if cooldown has not passed', function () {
    /** @var \Tests\TestCase $this */
    actingAs($this->designer);

    // Set cooldown to 30 seconds
    SystemSetting::firstOrCreate(['id' => 1])->update(['question_cooldown_seconds' => 30]);

    // Create a question 10 seconds ago
    Question::factory()->create([
        'user_id' => $this->designer->id,
        'created_at' => Carbon::now()->subSeconds(10),
    ]);

    $options = [
        ['option_number' => 1, 'text' => 'گزینه ۱'],
        ['option_number' => 2, 'text' => 'گزینه ۲'],
        ['option_number' => 3, 'text' => 'گزینه ۳'],
        ['option_number' => 4, 'text' => 'گزینه ۴'],
    ];

    Livewire::test(CreateQuestion::class)
        ->fillForm([
            'text' => 'متن سوال جدید',
            'category_id' => $this->category->id,
            'edition' => 'اول',
            'discipline' => 'civil',
            'qualification' => 'supervision',
            'exact_source' => 'Page 42',
            'correct_option' => 1,
            'options' => $options,
        ])
        ->call('create')
        ->assertNotified('لطفاً کمی صبر کنید');

    // Make sure it wasn't saved
    expect(Question::where('text', 'متن سوال جدید')->exists())->toBeFalse();
});

it('prevents submission if daily limit is reached', function () {
    /** @var \Tests\TestCase $this */
    actingAs($this->designer);

    // Set max per day to 2, and cooldown to 0 for testing
    SystemSetting::firstOrCreate(['id' => 1])->update([
        'max_questions_per_day' => 2,
        'question_cooldown_seconds' => 0,
    ]);

    // Create 2 questions today
    Question::factory()->count(2)->create([
        'user_id' => $this->designer->id,
        'created_at' => Carbon::now(),
    ]);

    $options = [
        ['option_number' => 1, 'text' => 'گزینه ۱'],
        ['option_number' => 2, 'text' => 'گزینه ۲'],
        ['option_number' => 3, 'text' => 'گزینه ۳'],
        ['option_number' => 4, 'text' => 'گزینه ۴'],
    ];

    Livewire::test(CreateQuestion::class)
        ->fillForm([
            'text' => 'متن سومین سوال',
            'category_id' => $this->category->id,
            'edition' => 'اول',
            'discipline' => 'civil',
            'qualification' => 'supervision',
            'exact_source' => 'Page 42',
            'correct_option' => 1,
            'options' => $options,
        ])
        ->call('create')
        ->assertNotified('خطا در ثبت');

    expect(Question::where('text', 'متن سومین سوال')->exists())->toBeFalse();
});
