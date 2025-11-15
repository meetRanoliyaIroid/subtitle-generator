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
     * Create a new job instance.
     */
    public function __construct(
        public Video $video,
        public array $languages = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->video->update(['status' => 'processing_subtitle']);

        $manageFile = new ManageFile;
        $subtitleLanguages = $this->video->subtitle_languages ?? [];
        $successCount = 0;
        $failedLanguages = [];

        // If no languages specified, generate with auto-detect (null language)
        if (empty($this->languages)) {
            $subtitlePath = $manageFile->generateVideoSubtitle($this->video->video_path);

            if ($subtitlePath) {
                $this->video->update([
                    'subtitle_path' => $subtitlePath,
                    'status' => 'subtitle_generated',
                ]);
                GenerateVideoWithSubtitlesJob::dispatch($this->video);
            } else {
                $this->video->update([
                    'status' => 'failed',
                    'error_message' => 'Failed to generate subtitle file.',
                ]);
            }

            return;
        }

        // Generate subtitles for each selected language
        foreach ($this->languages as $language) {
            $subtitlePath = $manageFile->generateVideoSubtitle($this->video->video_path, $language);

            if ($subtitlePath) {
                $subtitleLanguages[$language] = $subtitlePath;
                $successCount++;
            } else {
                $failedLanguages[] = $language;
            }
        }

        if ($successCount > 0) {
            // Set the first successful subtitle as the default subtitle_path for backward compatibility
            $firstLanguage = array_key_first($subtitleLanguages);
            $this->video->update([
                'subtitle_path' => $subtitleLanguages[$firstLanguage],
                'subtitle_languages' => $subtitleLanguages,
                'status' => 'subtitle_generated',
            ]);

            // Dispatch job to generate video with subtitles (using first language)
            GenerateVideoWithSubtitlesJob::dispatch($this->video, $firstLanguage);
        } else {
            $this->video->update([
                'status' => 'failed',
                'error_message' => 'Failed to generate subtitle files for all selected languages.',
            ]);
        }

        // Log failed languages if any
        if (! empty($failedLanguages)) {
            \Illuminate\Support\Facades\Log::warning('Failed to generate subtitles for languages: '.implode(', ', $failedLanguages));
        }
    }
}
