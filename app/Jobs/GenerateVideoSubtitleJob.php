<?php

namespace App\Jobs;

use App\Helpers\ManageFile;
use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateVideoSubtitleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600; // 1 hour

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Video $video,
        public ?string $language = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Use database lock to prevent race conditions
        $video = DB::transaction(function () {
            return Video::lockForUpdate()->find($this->video->id);
        });

        if (! $video) {
            Log::error("Video not found: {$this->video->id}");

            return;
        }

        // Update status only on first language processing
        $subtitleLanguages = $video->subtitle_languages ?? [];
        if (empty($subtitleLanguages)) {
            $video->update(['status' => 'processing_subtitle']);
        }

        $manageFile = new ManageFile;
        $subtitlePath = $manageFile->generateVideoSubtitle($video->video_path, $this->language);

        if ($subtitlePath) {
            // Use atomic JSON update to prevent race conditions
            $languageKey = $this->language ?? 'auto';
            $jsonPath = '$.'.$languageKey;

            // Atomically update the JSON field using raw SQL with proper parameter binding
            // JSON_SET will merge the new value without overwriting existing keys
            // This prevents race conditions when multiple jobs update simultaneously
            DB::statement(
                'UPDATE videos 
                SET subtitle_languages = JSON_SET(
                    COALESCE(subtitle_languages, "{}"),
                    ?,
                    ?
                )
                WHERE id = ?',
                [$jsonPath, $subtitlePath, $video->id]
            );

            // Set the first subtitle as the default subtitle_path for backward compatibility
            // Use atomic update to prevent race condition
            DB::statement(
                'UPDATE videos 
                SET subtitle_path = COALESCE(subtitle_path, ?)
                WHERE id = ? AND subtitle_path IS NULL',
                [$subtitlePath, $video->id]
            );

            // Refresh to get updated subtitle_languages
            $video->refresh();
            $currentLanguages = $video->subtitle_languages ?? [];

            Log::info("Subtitle stored for language: {$languageKey}, total languages: ".count($currentLanguages).', paths: '.json_encode($currentLanguages));

            // If this is the first subtitle generated, dispatch video generation job
            if (count($currentLanguages) === 1) {
                $firstLanguage = array_key_first($currentLanguages);
                $langForVideo = ($firstLanguage === 'auto') ? null : $firstLanguage;
                Log::info('Dispatching video generation job with language: '.($langForVideo ?? 'auto'));
                GenerateVideoWithSubtitlesJob::dispatch($video, $langForVideo);
            }

            // Update status
            $video->update(['status' => 'subtitle_generated']);
        } else {
            $errorMsg = $this->language
                ? "Failed to generate subtitle file for language: {$this->language}."
                : 'Failed to generate subtitle file.';

            // Only mark as failed if this was the only language
            if (empty($subtitleLanguages)) {
                $video->update([
                    'status' => 'failed',
                    'error_message' => $errorMsg,
                ]);
            } else {
                Log::warning($errorMsg);
            }
        }
    }
}
