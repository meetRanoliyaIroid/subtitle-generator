<?php

namespace App\Helpers;

use Exception;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManageFile
{
    protected $ffmpeg;

    protected $ffmpegPath;

    protected $ffprobePath;

    public function __construct()
    {
        $this->ffmpegPath = config('services.ffmpeg_path');
        $this->ffprobePath = config('services.ffprobe_path');

        // Initialize FFmpeg with custom paths
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => $this->ffmpegPath,
            'ffprobe.binaries' => $this->ffprobePath,
            'timeout' => 3600, // Optional: Increase timeout if processing large files
            'ffmpeg.threads' => 12,   // Optional: Adjust the number of threads for performance
        ]);
    }

    public function getRandomFileName()
    {
        return Str::orderedUuid();  // Generate a random UUID with time-based sorting
    }

    public function getFileDuration($file)
    {
        try {
            // Get the file path
            $filePath = $file->getPathname();

            // Open the media file
            $audio = $this->ffmpeg->open($filePath);

            // Get the duration in seconds
            $durationInSeconds = $audio->getFFProbe()
                ->format($filePath)
                ->get('duration');

            // Set the time in MM:SS format
            return gmdate('i:s', $durationInSeconds);
        } catch (Exception $e) {
            Log::emerge($e->getMessage());

            return null;
        }
    }

    /**
     * Compress an audio file
     *
     * @param  string  $inputPath
     * @param  string  $outputPath
     */
    public function compressAudio($file, $fileNamePath)
    {
        try {
            // Get the file path
            $filePath = $file->getPathname();

            // Open the file with FFmpeg
            $audio = $this->ffmpeg->open($filePath);

            // Set audio bitrate for compression
            $audioFormat = new \FFMpeg\Format\Audio\Mp3;
            $audioFormat->setAudioKiloBitrate(64); // Set desired bitrate; defaults to 128kbps if not set

            // Extract directory from the file path
            $directoryPath = dirname($fileNamePath);

            // Check if folder exists; if not, create it
            if (! Storage::disk('public')->exists($directoryPath)) {
                Storage::disk('public')->makeDirectory($directoryPath);
            }

            // Save the compressed file to a temporary path
            $compressedTempPath = $fileNamePath;
            $audio->save($audioFormat, storage_path('app/public/'.$compressedTempPath));

            // Store the compressed file in S3 (or Spaces)
            Storage::disk('spaces')->put($fileNamePath, file_get_contents(storage_path('app/public/'.$compressedTempPath)));

            // Optionally delete the temporary local file after storing
            unlink(storage_path('app/public/'.$compressedTempPath));

            return $fileNamePath; // Return the path of the stored file
        } catch (Exception $e) {
            Log::emergency($e->getMessage());

            return null;
        }
    }

    /**
     * Compress an video file
     *
     * @param  string  $inputPath
     * @param  string  $outputPath
     */
    public function compressVideo($file, $outputPath)
    {
        try {
            // Get the file path
            $filePath = $file->getPathname();
            $bitrate = '2500k';

            $command = "$this->ffmpegPath -i $filePath -b:v $bitrate -bufsize $bitrate $outputPath";

            system($command);

        } catch (Exception $e) {
            Log::emergency($e->getMessage());

            return null;
        }
    }

    public function generateVideoThumbnail($videoUrl)
    {
        try {
            // Path to store the generated thumbnail
            $thumbnailPath = storage_path('app/public/content/video_file/thumbnail');
            if (! file_exists($thumbnailPath)) {
                mkdir($thumbnailPath, 0777, true);
            }

            // Generate thumbnail using FFmpeg
            $thumbnailName = 'thumbnail_'.time().'.jpg';
            $thumbnailPath = $thumbnailPath.'/'.$thumbnailName;
            $command = "ffmpeg -i {$videoUrl} -ss 00:00:01 -vframes 1 {$thumbnailPath}";
            exec($command);

            // Store the thumbnail in S3 (or Spaces)
            Storage::disk('spaces')->put('content/video_file/thumbnail/'.$thumbnailName, file_get_contents($thumbnailPath));

            // Optionally delete the local thumbnail file after storing
            unlink($thumbnailPath);

            return 'content/video_file/thumbnail/'.$thumbnailName;
        } catch (\Exception $e) {
            Log::emergency($e->getMessage());

            return null;
        }

        return null;
    }

    /**
     * Generate video subtitle
     *
     * @param  string  $videoUrl
     * @param  string|null  $language  Language code (e.g., 'en', 'es', 'fr'). If null, auto-detect.
     */
    public function generateVideoSubtitle($videoUrl, ?string $language = null): ?string
    {
        try {
            // Set unlimited execution time for this process
            set_time_limit(0);
            ini_set('max_execution_time', 0);

            $fullVideoPath = storage_path('app/public/'.$videoUrl);
            $outputDir = storage_path('app/public/subtitles');
            $escapedInput = escapeshellarg($fullVideoPath);
            $escapedOutput = escapeshellarg($outputDir);

            if (! file_exists($outputDir)) {
                mkdir($outputDir, 0775, true);
            }

            // Build command with language parameter if provided
            $command = "/root/.local/bin/whisper $escapedInput --model tiny --output_format srt --output_dir $escapedOutput";

            if ($language !== null) {
                $escapedLanguage = escapeshellarg($language);
                $command .= " --language $escapedLanguage";
            }

            $command .= ' 2>&1';

            Log::info("Starting subtitle generation for video: {$videoUrl}, language: ".($language ?? 'auto'));

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode != 0) {
                Log::emergency('Failed to generate subtitle file: '.implode("\n", $output));

                return null;
            }

            Log::info("Subtitle generation completed for video: {$videoUrl}, language: ".($language ?? 'auto'));

            // Don't delete the video file - we need it for embedding subtitles
            // Get The Srt File Name
            $baseFileName = pathinfo($fullVideoPath, PATHINFO_FILENAME);
            $srtFileName = $baseFileName.($language ? '_'.$language : '').'.srt';

            return 'subtitles/'.$srtFileName;
        } catch (\Exception $e) {
            Log::emergency($e->getMessage());

            return null;
        }
    }

    /**
     * Embed subtitles into video
     *
     * @param  string  $videoPath
     * @param  string  $subtitlePath
     * @param  string  $outputPath
     */
    public function embedSubtitlesIntoVideo($videoPath, $subtitlePath, $outputPath): ?string
    {
        try {
            $fullVideoPath = storage_path('app/public/'.$videoPath);
            $fullSubtitlePath = storage_path('app/public/'.$subtitlePath);
            $fullOutputPath = storage_path('app/public/'.$outputPath);

            // Ensure output directory exists
            $outputDir = dirname($fullOutputPath);
            if (! file_exists($outputDir)) {
                mkdir($outputDir, 0775, true);
            }

            // Escape paths for shell command
            $escapedVideo = escapeshellarg($fullVideoPath);
            $escapedSubtitle = escapeshellarg($fullSubtitlePath);
            $escapedOutput = escapeshellarg($fullOutputPath);

            // Use FFmpeg to embed subtitles (hardcode subtitles)
            $command = "$this->ffmpegPath -i $escapedVideo -vf subtitles=$escapedSubtitle $escapedOutput 2>&1";
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode != 0) {
                Log::emergency('Failed to embed subtitles into video: '.implode("\n", $output));

                return null;
            }

            return $outputPath;
        } catch (\Exception $e) {
            Log::emergency($e->getMessage());

            return null;
        }
    }
}
