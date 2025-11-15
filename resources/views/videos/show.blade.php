@extends('layouts.app')

@section('title', 'Video Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Video Details</h5>
                <a href="{{ route('videos.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class='bx bx-arrow-back me-1'></i>Back
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Original Name</h6>
                        <p>{{ $video->original_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Status</h6>
                        <p>
                            <span class="status-badge status-{{ $video->status }}">
                                {{ ucfirst(str_replace('_', ' ', $video->status)) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Uploaded At</h6>
                        <p>{{ $video->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Last Updated</h6>
                        <p>{{ $video->updated_at->format('M d, Y H:i:s') }}</p>
                    </div>
                </div>

                @if($video->error_message)
                    <div class="alert alert-danger">
                        <strong>Error:</strong> {{ $video->error_message }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <h6 class="mb-3">Download Subtitles</h6>
                        @if($video->subtitle_languages && count($video->subtitle_languages) > 0)
                            <div class="mb-3">
                                @php
                                    $languageNames = [
                                        'en' => 'English',
                                        'es' => 'Spanish',
                                        'fr' => 'French',
                                        'de' => 'German',
                                        'it' => 'Italian',
                                        'pt' => 'Portuguese',
                                        'ru' => 'Russian',
                                        'ja' => 'Japanese',
                                        'ko' => 'Korean',
                                        'zh' => 'Chinese',
                                        'ar' => 'Arabic',
                                        'hi' => 'Hindi',
                                        'nl' => 'Dutch',
                                        'pl' => 'Polish',
                                        'tr' => 'Turkish',
                                        'vi' => 'Vietnamese',
                                    ];
                                @endphp
                                @foreach($video->subtitle_languages as $langCode => $subtitlePath)
                                    <a href="{{ route('videos.download-subtitle', ['video' => $video, 'language' => $langCode]) }}" class="btn btn-sm btn-success me-2 mb-2">
                                        <i class='bx bx-download me-1'></i>{{ $languageNames[$langCode] ?? strtoupper($langCode) }} (.srt)
                                    </a>
                                @endforeach
                            </div>
                        @elseif($video->subtitle_path)
                            <div class="mb-3">
                                <a href="{{ route('videos.download-subtitle', $video) }}" class="btn btn-success">
                                    <i class='bx bx-download me-2'></i>Download Subtitle (.srt)
                                </a>
                            </div>
                        @else
                            <div class="mb-3">
                                <button class="btn btn-secondary" disabled>
                                    <i class='bx bx-time me-2'></i>Subtitle Not Available
                                </button>
                            </div>
                        @endif

                        <h6 class="mb-3 mt-4">Download Video with Subtitles</h6>
                        @if($video->video_with_subtitles_path)
                            <a href="{{ route('videos.download-video', $video) }}" class="btn btn-primary">
                                <i class='bx bx-download me-2'></i>Download Video with Subtitles
                            </a>
                        @else
                            <button class="btn btn-secondary" disabled>
                                <i class='bx bx-time me-2'></i>Video with Subtitles Not Available
                            </button>
                        @endif
                    </div>
                </div>

                @if($video->video_with_subtitles_path)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="mb-3">Preview Video</h6>
                            <div class="ratio ratio-16x9">
                                <video controls class="w-100">
                                    <source src="{{ Storage::disk('public')->url($video->video_with_subtitles_path) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    </div>
                @elseif($video->video_path)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="mb-3">Original Video</h6>
                            <div class="ratio ratio-16x9">
                                <video controls class="w-100">
                                    <source src="{{ Storage::disk('public')->url($video->video_path) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row mt-4">
                    <div class="col-12">
                        <form action="{{ route('videos.destroy', $video) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this video?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class='bx bx-trash me-2'></i>Delete Video
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(in_array($video->status, ['uploaded', 'processing_subtitle', 'subtitle_generated', 'processing_video']))
    @push('scripts')
    <script>
        // Auto-refresh page every 5 seconds if video is processing
        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>
    @endpush
@endif
@endsection

