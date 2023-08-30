<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bayar_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('driver', 50)->nullable();
            $table->string('merchant_ref_id', 100)->nullable();
            $table->string('gateway_ref_id', 100)->nullable();
            $table->string('currency_code', 5)->nullable();
            $table->bigInteger('amount')->nullable();
            $table->integer('hit')->default(0);
            $table->smallInteger('payment_status')->nullable();
            $table->string('payment_description')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('description')->nullable();
            $table->json('extra')->nullable();
            $table->string('return_url')->nullable();
            $table->string('callback_url')->nullable();
            $table->json('response')->nullable();
            $table->json('callback_response')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bayar_payments');
    }
};
