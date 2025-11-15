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
        // Update status only on first language processing
        $subtitleLanguages = $this->video->subtitle_languages ?? [];
        if (empty($subtitleLanguages)) {
            $this->video->update(['status' => 'processing_subtitle']);
        }

        $manageFile = new ManageFile;
        $subtitlePath = $manageFile->generateVideoSubtitle($this->video->video_path, $this->language);

        if ($subtitlePath) {
            // Update subtitle_languages array
            $subtitleLanguages = $this->video->fresh()->subtitle_languages ?? [];
            if ($this->language) {
                $subtitleLanguages[$this->language] = $subtitlePath;
            } else {
                // For auto-detect, store as 'auto'
                $subtitleLanguages['auto'] = $subtitlePath;
            }

            // Set the first subtitle as the default subtitle_path for backward compatibility
            if (! $this->video->subtitle_path) {
                $this->video->update([
                    'subtitle_path' => $subtitlePath,
                ]);
            }

            // Update subtitle_languages
            $this->video->update([
                'subtitle_languages' => $subtitleLanguages,
            ]);

            // Refresh video to get latest subtitle_languages
            $this->video->refresh();
            $currentLanguages = $this->video->subtitle_languages ?? [];

            // If this is the first subtitle generated, dispatch video generation job
            // We'll use the first available language for video generation
            if (count($currentLanguages) === 1) {
                $firstLanguage = array_key_first($currentLanguages);
                $langForVideo = ($firstLanguage === 'auto') ? null : $firstLanguage;
                GenerateVideoWithSubtitlesJob::dispatch($this->video, $langForVideo);
            }

            // Update status
            $this->video->update(['status' => 'subtitle_generated']);
        } else {
            $errorMsg = $this->language
                ? "Failed to generate subtitle file for language: {$this->language}."
                : 'Failed to generate subtitle file.';

            // Only mark as failed if this was the only language
            if (empty($subtitleLanguages)) {
                $this->video->update([
                    'status' => 'failed',
                    'error_message' => $errorMsg,
                ]);
            } else {
                \Illuminate\Support\Facades\Log::warning($errorMsg);
            }
        }
    }
}
