<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('discipline')->nullable()->after('unique_code'); // رشته
            $table->string('qualification')->nullable()->after('discipline'); // صلاحیت
            $table->string('reference_year')->nullable()->after('category_id'); // ویرایش مرجع
            $table->string('chapter')->nullable()->after('reference_year'); // فصل مرجع
            $table->string('topic_details')->nullable()->after('chapter'); // موضوع دقیق
            $table->string('skill_type')->nullable()->after('topic_details'); // مهارت سنجش
            $table->text('other_references')->nullable()->after('exact_source'); // سایر منابع
            $table->text('time_reasoning')->nullable()->after('estimated_time'); // دلیل زمان
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'discipline',
                'qualification',
                'reference_year',
                'chapter',
                'topic_details',
                'skill_type',
                'other_references',
                'time_reasoning',
            ]);
        });
    }
};
