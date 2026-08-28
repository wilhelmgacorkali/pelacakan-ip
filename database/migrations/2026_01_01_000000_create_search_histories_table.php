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
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ip', 'phone'])->index();
            $table->string('query', 100)->index();
            $table->string('title', 255)->nullable();
            $table->json('result_json')->nullable();
            $table->string('client_ip', 45)->nullable();
            $table->string('status', 20)->default('success');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
