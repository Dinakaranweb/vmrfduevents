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
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->dropForeign(['department_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('department_id');
            });
        }

        if (Schema::hasTable('activity_submissions') && Schema::hasColumn('activity_submissions', 'department_id')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                try {
                    $table->dropForeign(['department_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('department_id');
            });
        }

        Schema::dropIfExists('departments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('college_id');
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('college_id');
            });
        }

        if (Schema::hasTable('activity_submissions') && !Schema::hasColumn('activity_submissions', 'department_id')) {
            Schema::table('activity_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('college_id');
            });
        }
    }
};
