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
        Schema::create('bayar_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('action', 100)->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->text('response_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bayar_requests');
    }
};
