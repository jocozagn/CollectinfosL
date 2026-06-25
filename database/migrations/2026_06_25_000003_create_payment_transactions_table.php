<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('payment_method');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('GNF');
            $table->unsignedBigInteger('djomy_amount');
            $table->string('status')->default('pending');
            $table->string('djomy_transaction_id')->nullable()->index();
            $table->string('redirect_url', 2048)->nullable();
            $table->json('cart_snapshot');
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
