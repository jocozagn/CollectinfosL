<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_products', function (Blueprint $table) {
            $table->boolean('is_subscribable')->default(false)->after('is_active');
            $table->decimal('price_eur', 10, 2)->nullable()->after('is_subscribable');
            $table->unsignedInteger('price_gnf')->nullable()->after('price_eur');
            $table->unsignedTinyInteger('billing_months')->default(1)->after('price_gnf');
            $table->unsignedTinyInteger('discount_percent')->default(10)->after('billing_months');
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('type', 20)->default('cart')->after('user_id');
        });

        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_product_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('payment_method', 40)->nullable();
            $table->string('payment_reference')->nullable();
            $table->decimal('price_eur', 10, 2);
            $table->unsignedInteger('price_gnf')->nullable();
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('site_products', function (Blueprint $table) {
            $table->dropColumn([
                'is_subscribable',
                'price_eur',
                'price_gnf',
                'billing_months',
                'discount_percent',
            ]);
        });
    }
};
