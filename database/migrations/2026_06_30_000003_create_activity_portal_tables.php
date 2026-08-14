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
        Schema::create('activity_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id')->unique(); // e.g. VMRF-2026-001
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('college_id')->constrained('colleges')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->date('activity_date');
            $table->string('event_category'); // Conference, Workshop, Cultural, Sports, Research, Guest Lecture, Extension/CSR, Award/Achievement, Other
            $table->text('tags')->nullable();
            $table->string('location')->nullable();
            $table->string('faculty_coordinator_name')->nullable();
            $table->string('faculty_coordinator_email')->nullable();
            $table->string('faculty_coordinator_phone')->nullable();
            $table->text('social_media_caption')->nullable();
            $table->string('status')->default('draft'); // draft, verification_pending, approval_pending, media_review, scheduled, published, sent_back, rejected
            $table->string('current_stage')->default('College Verifier');
            $table->text('admin_remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_submission_id')->constrained('activity_submissions')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type'); // image, video, document
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('dimensions')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_submission_id')->constrained('activity_submissions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // submitted, verified, approved, rejected, sent_back, edited, scheduled, published
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('published_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_submission_id')->constrained('activity_submissions')->onDelete('cascade');
            $table->foreignId('media_editor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('platforms')->nullable(); // JSON array of target platforms
            $table->text('published_caption')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('post_url_instagram')->nullable();
            $table->string('post_url_linkedin')->nullable();
            $table->string('post_url_twitter')->nullable();
            $table->string('package_zip_path')->nullable();
            $table->timestamps();
        });

        Schema::create('portal_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, success, warning, alert
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('recipient_email');
            $table->string('subject');
            $table->string('stage'); // submitted, verification_pending, media_review, published, sent_back
            $table->text('body_html');
            $table->string('status')->default('sent'); // sent, queued, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('portal_notifications');
        Schema::dropIfExists('published_posts');
        Schema::dropIfExists('approval_logs');
        Schema::dropIfExists('activity_media');
        Schema::dropIfExists('activity_submissions');
    }
};
