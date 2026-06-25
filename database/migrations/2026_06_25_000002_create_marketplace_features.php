<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('country')->nullable()->after('phone');
            $table->string('city')->nullable()->after('country');
            $table->string('profile_slug')->nullable()->unique()->after('city');
            $table->text('bio')->nullable()->after('profile_slug');
            $table->string('account_type')->nullable()->after('bio');
            $table->decimal('wallet_balance', 12, 2)->default(0)->after('account_type');
            $table->json('profile_meta')->nullable()->after('wallet_balance');
        });

        Schema::table('content_purchases', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('price');
            $table->string('payment_status')->default('completed')->after('payment_method');
            $table->string('payment_reference')->nullable()->after('payment_status');
            $table->string('invoice_number')->nullable()->unique()->after('payment_reference');
            $table->decimal('platform_fee', 10, 2)->default(0)->after('invoice_number');
            $table->decimal('journalist_earning', 10, 2)->default(0)->after('platform_fee');
        });

        Schema::create('content_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('summary');
            $table->string('type');
            $table->string('theme')->nullable();
            $table->string('category')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('keywords')->nullable();
            $table->string('access')->default('free');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('rights')->nullable();
            $table->boolean('negotiable')->default(false);
            $table->string('thumbnail')->nullable();
            $table->string('media_path')->nullable();
            $table->string('file_format')->nullable();
            $table->string('resolution')->nullable();
            $table->string('duration')->nullable();
            $table->string('file_size')->nullable();
            $table->date('content_date')->nullable();
            $table->date('exclusivity_expires_at')->nullable();
            $table->string('gps_lat')->nullable();
            $table->string('gps_lng')->nullable();
            $table->string('status')->default('pending');
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('content_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_journalist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('type')->nullable();
            $table->string('theme')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->text('delivery_note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('content_orders');
        Schema::dropIfExists('content_submissions');

        Schema::table('content_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'payment_reference',
                'invoice_number',
                'platform_fee',
                'journalist_earning',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'country',
                'city',
                'profile_slug',
                'bio',
                'account_type',
                'wallet_balance',
                'profile_meta',
            ]);
        });
    }
};
