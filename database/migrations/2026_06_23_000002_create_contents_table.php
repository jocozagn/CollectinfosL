<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('type')->default('video'); // video, article, audio, photo, infographic
            $table->string('theme')->nullable(); // politique, economie, societe, securite, culture, environnement
            $table->string('country')->nullable();
            $table->string('category')->nullable(); // experts, medias, organisations, particuliers
            $table->string('access')->default('free'); // free, subscriber
            $table->decimal('price', 8, 2)->nullable();
            $table->string('duration')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('media_path')->nullable();
            $table->string('status')->default('draft'); // draft, published
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
