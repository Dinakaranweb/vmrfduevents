<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->string('report_type')->nullable()->after('activity_type');
            $table->string('resource_person_name')->nullable()->after('report_type');
            $table->text('outcome')->nullable()->after('resource_person_name');
        });
    }

    public function down(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->dropColumn(['report_type', 'resource_person_name', 'outcome']);
        });
    }
};
