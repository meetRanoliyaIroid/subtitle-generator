@extends('layouts.app')

@section('title', 'Videos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Video List</h5>
                <a href="{{ route('videos.create') }}" class="btn btn-primary">
                    <i class='bx bx-upload me-2'></i>Upload Video
                </a>
            </div>
            <div class="card-body">
                @if($videos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Original Name</th>
                                    <th>Status</th>
                                    <th>Uploaded At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($videos as $video)
                                    <tr>
                                        <td>{{ $video->original_name }}</td>
                                        <td>
                                            <span class="status-badge status-{{ $video->status }}">
                                                {{ ucfirst(str_replace('_', ' ', $video->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $video->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('videos.show', $video) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class='bx bx-show'></i> View
                                                </a>
                                                @if($video->subtitle_path)
                                                    <a href="{{ route('videos.download-subtitle', $video) }}" class="btn btn-sm btn-outline-success">
                                                        <i class='bx bx-download'></i> Subtitle
                                                    </a>
                                                @endif
                                                @if($video->video_with_subtitles_path)
                                                    <a href="{{ route('videos.download-video', $video) }}" class="btn btn-sm btn-outline-info">
                                                        <i class='bx bx-download'></i> Video
                                                    </a>
                                                @endif
                                                <form action="{{ route('videos.destroy', $video) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class='bx bx-trash'></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $videos->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class='bx bx-video bx-lg text-muted mb-3'></i>
                        <p class="text-muted">No videos uploaded yet.</p>
                        <a href="{{ route('videos.create') }}" class="btn btn-primary">
                            <i class='bx bx-upload me-2'></i>Upload Your First Video
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

