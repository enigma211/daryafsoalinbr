<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('unique_code')->unique();
            $table->string('title')->nullable();
            $table->text('text');
            $table->enum('type', ['multiple_choice', 'descriptive'])->default('multiple_choice');
            $table->tinyInteger('correct_option')->nullable(); // 1-4
            $table->text('descriptive_answer')->nullable();
            $table->string('exact_source')->nullable();
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->nullable();
            $table->integer('estimated_time')->nullable(); // in seconds or minutes
            $table->json('keywords')->nullable();
            $table->enum('current_status', [
                'draft', 'incomplete', 'awaiting_review', 'scientific_review', 
                'regulations_review', 'needs_revision', 'approved', 'rejected', 'archived'
            ])->default('draft');
            
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // designer
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null'); // related topic
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
