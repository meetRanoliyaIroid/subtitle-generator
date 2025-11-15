<?php

namespace App\Jobs;

use App\Helpers\ManageFile;
use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GenerateVideoWithSubtitlesJob implements ShouldQueue
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
        $this->video->update(['status' => 'processing_video']);

        $manageFile = new ManageFile;

        // Get subtitle path based on language
        $subtitlePath = null;
        if ($this->language && $this->video->subtitle_languages && isset($this->video->subtitle_languages[$this->language])) {
            $subtitlePath = $this->video->subtitle_languages[$this->language];
        } else {
            // Fallback to default subtitle_path
            $subtitlePath = $this->video->subtitle_path;
        }

        if (! $subtitlePath) {
            $this->video->update([
                'status' => 'failed',
                'error_message' => 'Subtitle file not found for video generation.',
            ]);

            return;
        }

        $languageSuffix = $this->language ? '_'.$this->language : '';
        $outputPath = 'videos_with_subtitles/'.Str::uuid().$languageSuffix.'.mp4';

        $result = $manageFile->embedSubtitlesIntoVideo(
            $this->video->video_path,
            $subtitlePath,
            $outputPath
        );

        if ($result) {
            $this->video->update([
                'video_with_subtitles_path' => $result,
                'status' => 'completed',
            ]);
        } else {
            $this->video->update([
                'status' => 'failed',
                'error_message' => 'Failed to embed subtitles into video.',
            ]);
        }
    }
}
