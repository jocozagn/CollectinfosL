<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('preview_enabled')->default(true)->after('media_path');
            $table->unsignedSmallInteger('preview_seconds')->default(15)->after('preview_enabled');
            $table->text('preview_excerpt')->nullable()->after('preview_seconds');
            $table->string('preview_media_path')->nullable()->after('preview_excerpt');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn([
                'preview_enabled',
                'preview_seconds',
                'preview_excerpt',
                'preview_media_path',
            ]);
        });
    }
};
