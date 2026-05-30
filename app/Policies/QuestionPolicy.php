<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view questions');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Question $question): bool
    {
        if ($user->hasRole(['Super Admin', 'Exam Manager', 'Operator'])) {
            return true;
        }

        if ($user->hasRole('Question Designer') && $question->user_id === $user->id) {
            return true;
        }

        if ($user->hasRole(['Scientific Reviewer', 'Regulations Reviewer', 'Field Secretary'])) {
            // Later we can check if it's assigned to them or their field
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create questions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Question $question): bool
    {
        if ($user->hasRole(['Super Admin', 'Exam Manager'])) {
            return true;
        }

        if ($user->hasRole('Question Designer')) {
            // Designer can only edit if it is their question AND it's draft or needs revision
            return $question->user_id === $user->id && in_array($question->current_status, ['draft', 'needs_revision']);
        }

        if ($user->hasRole('Field Secretary')) {
            return true;
        }

        if ($user->hasRole(['Scientific Reviewer', 'Regulations Reviewer'])) {
            // Reviewers can't edit the question itself directly, they just add comments, 
            // but we might allow them to update status. For now, deny edit on the form.
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Question $question): bool
    {
        if ($user->hasRole(['Super Admin', 'Exam Manager'])) {
            return true;
        }

        if ($user->hasRole('Question Designer')) {
            // Designer can delete only if it's a draft
            return $question->user_id === $user->id && $question->current_status === 'draft';
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Question $question): bool
    {
        return $user->hasRole(['Super Admin', 'Exam Manager']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Question $question): bool
    {
        return $user->hasRole(['Super Admin']);
    }
}
