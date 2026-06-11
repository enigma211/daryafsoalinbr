<?php

use App\Models\User;
use App\Models\Question;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $permissions = ['view questions', 'create questions', 'edit questions', 'delete questions'];
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $designerRole = Role::firstOrCreate(['name' => 'Question Designer', 'guard_name' => 'web']);

    $this->designer = User::factory()->create();
    $this->designer->assignRole('Question Designer');
});

it('soft deletes a question and allows restoration', function () {
    /** @var \Tests\TestCase $this */
    $question = Question::factory()->create([
        'user_id' => $this->designer->id,
        'current_status' => 'draft',
    ]);

    // Soft delete the question
    $question->delete();

    // Verify it's no longer in normal queries
    expect(Question::find($question->id))->toBeNull();

    // Verify it's in the trashed items
    $trashedQuestion = Question::withTrashed()->find($question->id);
    expect($trashedQuestion)->not->toBeNull();
    expect($trashedQuestion->deleted_at)->not->toBeNull();

    // Verify it still exists in the database
    $this->assertSoftDeleted('questions', [
        'id' => $question->id,
    ]);

    // Restore the question
    $trashedQuestion->restore();

    // Verify it's back in normal queries
    expect(Question::find($question->id))->not->toBeNull();
    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'deleted_at' => null,
    ]);
});

it('cascades deletes questions when the designer is deleted', function () {
    /** @var \Tests\TestCase $this */
    $question = Question::factory()->create([
        'user_id' => $this->designer->id,
    ]);

    // Ensure it exists
    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
    ]);

    // Delete the user
    $this->designer->delete();

    // SQLite in-memory with foreign key constraints enabled will cascade
    $this->assertDatabaseMissing('questions', [
        'id' => $question->id,
    ]);
});
