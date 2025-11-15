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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('video_path');
            $table->string('subtitle_path')->nullable();
            $table->string('video_with_subtitles_path')->nullable();
            $table->enum('status', ['uploaded', 'processing_subtitle', 'subtitle_generated', 'processing_video', 'completed', 'failed'])->default('uploaded');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
