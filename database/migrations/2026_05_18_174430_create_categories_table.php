<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('topic')->nullable(); // مبحث
            $table->string('edition')->nullable(); // ویرایش
            $table->string('chapter')->nullable(); // فصل
            $table->string('clause')->nullable(); // بند
            $table->string('page')->nullable(); // صفحه
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
