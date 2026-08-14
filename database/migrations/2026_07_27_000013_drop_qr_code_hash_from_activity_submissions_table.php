<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('activity_submissions', 'qr_code_hash')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->dropColumn('qr_code_hash');
            });
        }
    }

    public function down(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->string('qr_code_hash')->nullable();
        });
    }
};
