<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->string('country')->nullable();
            $table->string('theme')->nullable();
            $table->unsignedSmallInteger('places')->default(3);
            $table->string('status')->default('open'); // open, closed
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('collaboration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // join, propose
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->text('message');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_requests');
        Schema::dropIfExists('investigations');
    }
};
