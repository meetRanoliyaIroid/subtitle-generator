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
        public Video $video
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->video->update(['status' => 'processing_subtitle']);

        $manageFile = new ManageFile();
        $subtitlePath = $manageFile->generateVideoSubtitle($this->video->video_path);

        if ($subtitlePath) {
            $this->video->update([
                'subtitle_path' => $subtitlePath,
                'status' => 'subtitle_generated',
            ]);

            // Dispatch job to generate video with subtitles
            GenerateVideoWithSubtitlesJob::dispatch($this->video);
        } else {
            $this->video->update([
                'status' => 'failed',
                'error_message' => 'Failed to generate subtitle file.',
            ]);
        }
    }
}
