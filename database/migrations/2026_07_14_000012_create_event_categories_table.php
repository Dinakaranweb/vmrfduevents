<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_categories')) {
            Schema::create('event_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed initial master categories
            $categories = [
                ['name' => 'Academic Conference & Seminar', 'slug' => 'academic-conference', 'sort_order' => 1, 'description' => 'National and international academic conferences, seminars, and symposiums.'],
                ['name' => 'Workshop & Hands-on Training', 'slug' => 'workshop-training', 'sort_order' => 2, 'description' => 'Skill development workshops, lab sessions, and professional training programs.'],
                ['name' => 'Sports & Athletics Event', 'slug' => 'sports-athletics', 'sort_order' => 3, 'description' => 'Inter-college, university, and state level sports tournaments and athletic meets.'],
                ['name' => 'Cultural & Fest Celebrations', 'slug' => 'cultural-fest', 'sort_order' => 4, 'description' => 'Annual college fests, cultural competitions, music, dance, and arts activities.'],
                ['name' => 'Community Outreach & NSS / NCC', 'slug' => 'outreach-nss', 'sort_order' => 5, 'description' => 'Social welfare camps, health checkup drives, NSS, and NCC community service.'],
                ['name' => 'Research & Innovation Symposium', 'slug' => 'research-symposium', 'sort_order' => 6, 'description' => 'Research paper presentations, patent exhibitions, and innovation challenges.'],
                ['name' => 'Convocation & Institutional Ceremony', 'slug' => 'convocation-ceremony', 'sort_order' => 7, 'description' => 'Graduation ceremonies, foundation days, and institutional award functions.'],
                ['name' => 'Guest Lecture & Alumni Interaction', 'slug' => 'guest-lecture', 'sort_order' => 8, 'description' => 'Distinguished guest lectures, industry expert talks, and alumni networking sessions.'],
            ];

            foreach ($categories as $cat) {
                DB::table('event_categories')->insert(array_merge($cat, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
