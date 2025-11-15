<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Jobs\GenerateVideoSubtitleJob;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $video = Video::create([
            'original_name' => $fileName,
            'video_path' => $filePath,
            'status' => 'uploaded',
        ]);

        // Dispatch job to generate subtitle
        GenerateVideoSubtitleJob::dispatch($video);

        return redirect()->route('videos.index')
            ->with('success', 'Video uploaded successfully. Subtitle generation has started.');
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
    public function downloadSubtitle(Video $video)
    {
        if (!$video->subtitle_path || !Storage::disk('public')->exists($video->subtitle_path)) {
            return redirect()->back()->with('error', 'Subtitle file not found.');
        }

        return Storage::disk('public')->download($video->subtitle_path, $video->original_name . '.srt');
    }

    /**
     * Download video with subtitles
     */
    public function downloadVideo(Video $video)
    {
        if (!$video->video_with_subtitles_path || !Storage::disk('public')->exists($video->video_with_subtitles_path)) {
            return redirect()->back()->with('error', 'Video with subtitles not found.');
        }

        $originalName = pathinfo($video->original_name, PATHINFO_FILENAME);
        $downloadName = $originalName . '_with_subtitles.mp4';

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
        if ($video->video_with_subtitles_path && Storage::disk('public')->exists($video->video_with_subtitles_path)) {
            Storage::disk('public')->delete($video->video_with_subtitles_path);
        }

        $video->delete();

        return redirect()->route('videos.index')
            ->with('success', 'Video deleted successfully.');
    }
}
