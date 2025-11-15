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
        $this->video->update(['status' => 'processing_video']);

        $manageFile = new ManageFile();
        $outputPath = 'videos_with_subtitles/' . Str::uuid() . '.mp4';

        $result = $manageFile->embedSubtitlesIntoVideo(
            $this->video->video_path,
            $this->video->subtitle_path,
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
