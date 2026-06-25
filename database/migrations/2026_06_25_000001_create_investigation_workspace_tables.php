<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investigation_participants', function (Blueprint $table) {
            $table->string('role')->default('contributor')->after('collaboration_request_id');
        });

        Schema::create('investigation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['investigation_id', 'created_at']);
        });

        Schema::create('investigation_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['investigation_id', 'created_at']);
        });

        Schema::create('investigation_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('status')->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['investigation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_drafts');
        Schema::dropIfExists('investigation_files');
        Schema::dropIfExists('investigation_messages');

        Schema::table('investigation_participants', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
