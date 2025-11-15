<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Jobs\GenerateVideoSubtitleJob;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $videos = Video::latest()->paginate(10);

        return view('videos.index', compact('videos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('videos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $file = $request->file('video');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('videos', 'public');
        $languages = $request->input('languages', []);

        $video = Video::create([
            'original_name' => $fileName,
            'video_path' => $filePath,
            'status' => 'uploaded',
            'subtitle_languages' => [],
        ]);

        // Dispatch job to generate subtitle for each selected language
        GenerateVideoSubtitleJob::dispatch($video, $languages);

        $languageCount = count($languages);
        $message = $languageCount > 0
            ? "Video uploaded successfully. Subtitle generation has started for {$languageCount} language(s)."
            : 'Video uploaded successfully. Subtitle generation has started (auto-detect).';

        return redirect()->route('videos.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video): View
    {
        return view('videos.show', compact('video'));
    }

    /**
     * Download subtitle file
     */
    public function downloadSubtitle(Video $video, ?string $language = null)
    {
        // If language is specified, get from subtitle_languages array
        if ($language && $video->subtitle_languages && isset($video->subtitle_languages[$language])) {
            $subtitlePath = $video->subtitle_languages[$language];
            if (Storage::disk('public')->exists($subtitlePath)) {
                $originalName = pathinfo($video->original_name, PATHINFO_FILENAME);

                return Storage::disk('public')->download($subtitlePath, $originalName.'_'.$language.'.srt');
            }
        }

        // Fallback to old subtitle_path for backward compatibility
        if ($video->subtitle_path && Storage::disk('public')->exists($video->subtitle_path)) {
            return Storage::disk('public')->download($video->subtitle_path, $video->original_name.'.srt');
        }

        return redirect()->back()->with('error', 'Subtitle file not found.');
    }

    /**
     * Download video with subtitles
     */
    public function downloadVideo(Video $video)
    {
        if (! $video->video_with_subtitles_path || ! Storage::disk('public')->exists($video->video_with_subtitles_path)) {
            return redirect()->back()->with('error', 'Video with subtitles not found.');
        }

        $originalName = pathinfo($video->original_name, PATHINFO_FILENAME);
        $downloadName = $originalName.'_with_subtitles.mp4';

        return Storage::disk('public')->download($video->video_with_subtitles_path, $downloadName);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video): RedirectResponse
    {
        // Delete files
        if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
            Storage::disk('public')->delete($video->video_path);
        }
        if ($video->subtitle_path && Storage::disk('public')->exists($video->subtitle_path)) {
            Storage::disk('public')->delete($video->subtitle_path);
        }
        // Delete all subtitle language files
        if ($video->subtitle_languages && is_array($video->subtitle_languages)) {
            foreach ($video->subtitle_languages as $subtitlePath) {
                if (Storage::disk('public')->exists($subtitlePath)) {
                    Storage::disk('public')->delete($subtitlePath);
                }
            }
        }
        if ($video->video_with_subtitles_path && Storage::disk('public')->exists($video->video_with_subtitles_path)) {
            Storage::disk('public')->delete($video->video_with_subtitles_path);
        }

        $video->delete();

        return redirect()->route('videos.index')
            ->with('success', 'Video deleted successfully.');
    }
}
