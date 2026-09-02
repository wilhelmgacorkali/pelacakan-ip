<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Identitas peminta lokasi (ditampilkan jelas di halaman Device Agent
            // supaya penerima link tahu persis siapa yang meminta & untuk apa).
            $table->string('requester_name', 120)->nullable()->after('phone');
            $table->string('requester_photo_url', 500)->nullable()->after('requester_name');
            $table->string('purpose', 200)->nullable()->after('requester_photo_url');
            $table->boolean('sharing_enabled')->default(true)->after('is_active');
            $table->timestamp('sharing_revoked_at')->nullable()->after('sharing_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['requester_name', 'requester_photo_url', 'purpose', 'sharing_enabled', 'sharing_revoked_at']);
        });
    }
};
