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
                        <label class="form-label">Select Languages for Subtitle Generation</label>
                        <div class="form-text mb-2">Select one or more languages. If none selected, subtitle will be auto-detected.</div>
                        <div class="row">
                            @php
                                $languages = [
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
                            @foreach($languages as $code => $name)
                                <div class="col-md-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="languages[]" value="{{ $code }}" id="lang_{{ $code }}">
                                        <label class="form-check-label" for="lang_{{ $code }}">
                                            {{ $name }} ({{ strtoupper($code) }})
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('languages.*')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
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

