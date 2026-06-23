<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5);
            $table->string('translatable_type', 100);
            $table->unsignedBigInteger('translatable_id');
            $table->string('field', 50);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'translations_unique');
            $table->index(['translatable_type', 'translatable_id'], 'translations_target_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
