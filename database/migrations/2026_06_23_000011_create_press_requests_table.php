<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('press_requests', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('email');
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->text('company_experience')->nullable();
            $table->json('topics');
            $table->string('topics_other')->nullable();
            $table->string('country');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('press_requests');
    }
};
