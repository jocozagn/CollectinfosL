<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investigations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('collaboration_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('proposed_title')->nullable()->after('type');
        });

        Schema::create('investigation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collaboration_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['investigation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_participants');

        Schema::table('collaboration_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('proposed_title');
        });

        Schema::table('investigations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
