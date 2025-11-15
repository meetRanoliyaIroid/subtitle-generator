@extends('layouts.app')

@section('title', 'Upload Video')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Upload Video</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="video" class="form-label">Select Video File</label>
                        <input type="file" class="form-control @error('video') is-invalid @enderror" id="video" name="video" accept="video/*" required>
                        @error('video')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Supported formats: MP4, AVI, MOV, WMV, FLV, WEBM (Max: 100MB)</div>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-upload me-2'></i>Upload Video
                        </button>
                        <a href="{{ route('videos.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

